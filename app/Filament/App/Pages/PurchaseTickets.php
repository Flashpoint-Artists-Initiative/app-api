<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

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
                    Wizard\Step::make('Purchase')
                        ->schema([
                            TextInput::make('slug'),
                        ]),
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

    protected function buildWaiverStep(): Step
    {
        /** @var string $username */
        $username = Auth::user()?->legal_name;

        return Wizard\Step::make('Waivers')
            ->schema([
                Placeholder::make('waiver')
                    ->content($this->getWaiverContent())
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
            ]);
    }

    protected function getWaiverContent(): HtmlString
    {
        if (! $this->waiver) {
            return new HtmlString('No waiver is required for this event.');
        }

        return new HtmlString($this->waiver->content);
    }

    protected function createCart(CartService $cartService): void
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
        $tickets = (new Collection($this->data['tickets'] ?? []))->map(function ($id, $quantity) {
            return [
                'id' => $id,
                'quantity' => $quantity,
            ];
        })->toArray();
        $reserved = (new Collection($this->data['reserved'] ?? []))->filter(fn ($value) => $value === true)->keys()->toArray();

        $cartService->createCartAndItems($tickets, $reserved);
    }

    public function mount(): void
    {
        $this->form->fill();
    }
}
