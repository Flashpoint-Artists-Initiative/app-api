<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Forms\Components\StripeCheckout;
use App\Models\Event;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\Ticketing\Waiver;
use App\Rules\TicketSaleRule;
use App\Services\CartService;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use App\Models\User;
use App\Services\StripeService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Support\Assets\Js;

/**
 * @property Form $form
 */
class PurchaseTickets extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static string $view = 'filament.app.pages.purchase-tickets';

    protected static ?string $slug = 'purchase';

    // @phpstan-ignore-next-line Required by parent class
    protected $listeners = [
        'active-event-updated' => '$refresh',
    ];

    /** @var array<string, mixed> */
    public array $data = [];

    protected ?Waiver $waiver;

    public function form(Form $form): Form
    {
        $this->waiver = Event::getCurrentEvent()?->waivers()->first();

        return $form
            ->schema([
                Wizard::make([
                    $this->buildTicketsStep(),
                    $this->buildWaiverStep(),
                    $this->buildPurchaseStep(),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                    <x-filament::button
                        type="submit"
                        size="sm"
                    >
                        Submit
                    </x-filament::button>
                BLADE))),
            ])
            ->statePath('data');
    }

    protected function buildTicketsStep(): Step
    {
        $tickets = TicketType::query()->currentEvent()->available()->get();
        $ticketSchema = $tickets->map(function (TicketType $ticket) {
            return ViewField::make('tickets.' . $ticket->id)
                ->model($ticket)
                ->default(0)
                ->rules([new TicketSaleRule])
                ->hiddenLabel()
                ->view('forms.components.ticket-type-field');
        });

        $reserved = ReservedTicket::query()->currentUser()->currentEvent()->canBePurchased()->get();
        $reservedSchema = $reserved->map(function (ReservedTicket $ticket) {
            return ViewField::make('reserved.' . $ticket->id)
                ->model($ticket)
                ->default(0)
                ->rules([new TicketSaleRule])
                ->hiddenLabel()
                ->view('forms.components.reserved-ticket-field')
                ->viewData(['expirationDate' => Carbon::parse($ticket->expiration_date)->toDayDateTimeString()]);
        });

        if ($reservedSchema->count() > 0) {
            $schema = [
                Section::make('General Sale Tickets')
                    ->schema($ticketSchema->toArray()),
                Section::make('Your Reserved Tickets')
                    ->description('These Tickets are reserved for you specifically.')
                    ->schema($reservedSchema->toArray()),
            ];
        } else {
            $schema = $ticketSchema->toArray();
        }

        return Wizard\Step::make('Select Tickets')
            ->schema($schema)
            ->afterValidation($this->createCart(...));
    }

    /**
     * Create the waiver step of the form wizard.  Hidden if no waiver is found or the user has already signed it.
     */
    protected function buildWaiverStep(): Step
    {
        /** @var User */
        $user = Auth::user();
        $username = $user->legal_name;

        return Wizard\Step::make('Waivers')
            ->schema([
                Placeholder::make('waiver')
                    ->content(new HtmlString($this->waiver->content ?? ''))
                    ->label(''),
                TextInput::make('signature')
                    ->label('I agree to the terms of the waiver and understand that I am signing this waiver electronically.')
                    ->helperText("You must enter your full legal name as it's your ID.")
                    ->required()
                    ->in([$username])
                    ->validationMessages([
                        'in' => 'The entered value must match your legal name, as listed in your profile.',
                    ])
                    ->hidden($this->waiver === null),
            ])  
            ->hidden(fn() => ! $this->waiver || $user->waivers()->where('waiver_id', $this->waiver->id)->count() > 0)
            ->afterValidation($this->createCompletedWaiver(...));
    }

    protected function buildPurchaseStep(): Step
    {
        return Wizard\Step::make('Purchase')
            ->schema([
                Hidden::make('checkout_session_secret'),
                StripeCheckout::make('stripe_checkout')
                    ->checkoutSecret(fn(Get $get) => $get('checkout_session_secret'))
                    ->label(''),
            ]);
    }

    protected function createCart(Set $set, CartService $cartService, StripeService $stripeService): void
    {
        /**
         * We receive data in this format:
         * $data['tickets'] = [
         *    $id => $quantity
         * ]
         * $data['reserved'] = [
         *    $id => $boolean
         * ]
         *
         * We need to get the data into this format:
         * $tickets = [
         *    ['id' => $id, 'quantity' => $quantity],
         * ]
         *
         * $reserved = [$id, $id, $id]
         */
        $tickets = (new Collection($this->data['tickets'] ?? []))->map(function ($quantity, $id) {
            return [
                'id' => $id,
                'quantity' => $quantity,
            ];
        })->toArray();
        $reserved = (new Collection($this->data['reserved'] ?? []))->filter(fn ($value) => $value === true)->keys()->toArray();

        $cartService->expireAllUnexpiredCarts();
        $cart = $cartService->createCartAndItems($tickets, $reserved);
        $session = $stripeService->createCheckoutFromCart($cart);
        $cart->setStripeCheckoutIdAndSave($session->id);

        $set('checkout_session_secret', $session->client_secret);
        $api_key = config('services.stripe.api_key');
        $this->js(<<<JS
            stripe = Stripe('{$api_key}');


            initialize();

            // Create a Checkout Session
            async function initialize() {
                const fetchClientSecret = async () => {
                    return '{$session->client_secret}';
                };

                const checkout = await stripe.initEmbeddedCheckout({
                    fetchClientSecret,
                });

                // Mount Checkout
                checkout.mount('#stripe-checkout');
            }
        JS);
    }

    protected function createCompletedWaiver(): void
    {
        if ($this->waiver) {
            $this->waiver->completedWaivers()->create([
                'user_id' => Auth::id(),
                'form_data' => [
                    'signature' => $this->data['signature'],
                ],
            ]);
        }
    }

    public function mount(): void
    {
        $this->form->fill();
    }
}
