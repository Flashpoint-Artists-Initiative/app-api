<?php
declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Filament\Actions\CreateCartAction;
use App\Forms\Components\TicketTypeField;
use App\Models\Event;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Rules\TicketSaleRule;
use App\Services\CartService;
use Blade;
use Filament\Pages\Page;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Livewire\Component;

/**
 * @property Form $form
 */
class PurchaseTickets extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static string $view = 'filament.app.pages.purchase-tickets';

    protected static ?string $slug = 'purchase';

    /** @var array<string, mixed> $data */
    public array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    $this->buildTicketsStep(),
                    Wizard\Step::make('Waivers')
                        ->schema([
                            RichEditor::make('content'),
                        ]),
                    Wizard\Step::make('Purchase')
                        ->schema([
                            TextInput::make('slug'),
                        ]),
                ])
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button
                        type="submit"
                        size="sm"
                    >
                        Submit
                    </x-filament::button>
                BLADE)))
            ])
            ->statePath('data');
    }

    protected function buildTicketsStep(): Step
    {
        $tickets = TicketType::query()->event(session('active_event_id'))->available()->get();
        $ticketSchema = $tickets->map(function (TicketType $ticket) {
            return ViewField::make('tickets.'.$ticket->id)
                ->model($ticket)
                ->default(0)
                ->rules([new TicketSaleRule()])
                ->hiddenLabel()
                ->view('forms.components.ticket-type-field');
        });

        $reserved = ReservedTicket::query()->currentUser()->event(session('active_event_id'))->canBePurchased()->get();
        $reservedSchema = $reserved->map(function (ReservedTicket $ticket) {
            return ViewField::make('reserved.'.$ticket->id)
                ->model($ticket)
                ->default(0)
                ->rules([new TicketSaleRule()])
                ->hiddenLabel()
                ->view('forms.components.reserved-ticket-field');
        });

        if ($reservedSchema->count() > 0) {
            $schema = [
                Section::make('General Sale Tickets')
                    ->schema($ticketSchema->toArray()),
                Section::make('Your Reserved Tickets')
                    ->description('These Tickets are reserved for you specifically.')
                    ->schema($reservedSchema->toArray())
            ];
        } else {
            $schema = $ticketSchema->toArray();
        }

        return Wizard\Step::make('Select Tickets')
            ->schema($schema)
            ->afterValidation($this->createCart(...));
    }

    protected function createCart(PurchaseTickets $livewire, CartService $cartService): void
    {
        /**
         * We receive data in this format:
         * $data['tickets'] = [
         *    `id` => `quantity`
         * ]
         * $data['reserved'] = [
         *    `id` => `boolean`
         * ]
         */
        $data = $livewire->form->getState();

        /**
         * We need to get the data into this format:
         * $tickets = [
         *    ['id' => $id, 'quantity' => $quantity],
         * ]
         * 
         * $reserved = [$id, $id, $id]
         */

        $tickets = (new Collection($data['tickets'] ?? []))->map(function ($id, $quantity) {
            return [
                'id' => $id,
                'quantity' => $quantity,
            ];
        })->toArray();
        $reserved = (new Collection($data['reserved'] ?? []))->filter(fn ($value) => $value === true)->keys()->toArray();

        $cartService->createCartAndItems($tickets, $reserved);
    }

 
    public function mount(): void
    {
        $this->form->fill();
    }
}
