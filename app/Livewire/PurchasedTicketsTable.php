<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Event;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class PurchasedTicketsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    // @phpstan-ignore-next-line Required by parent class
    protected $listeners = [
        'active-event-updated' => '$refresh',
    ];

    public function render(): View
    {
        return view('livewire.purchased-tickets-table');
    }

    public function table(Table $table): Table
    {
        $currentEvent = Event::getCurrentEvent();
        $url = route('filament.app.pages.purchase');

        if (! $currentEvent) {
            $emptyDescription = 'No event is currently active.';
        } else {
            $emptyDescription = "You have not purchased any tickets for {$currentEvent->name}.";
        }

        return $table
            ->query(PurchasedTicket::query()->currentUser()->currentEvent())
            ->columns([
                TextColumn::make('ticketType.name')
                    ->label('Ticket Type')
                    ->description(fn (ReservedTicket $ticket) => str($ticket->note)->limit(50)),
                TextColumn::make('final_expiration_date')
                    ->label('Expiration Date')
                    ->dateTime('F jS, Y g:i A T', 'America/New_York'),
                TextColumn::make('ticketType.price')
                    ->label('Price')
                    ->money('USD'),
            ])
            ->actions([
                TableAction::make('purchase')
                    ->label('Purchase')
                    ->url(fn (ReservedTicket $ticket) => route('filament.app.pages.purchase', ['ticket' => $ticket->id])),
                TableAction::make('transfer')
                    ->label('Transfer')
                    ->color(Color::Blue)
                    ->url(fn (ReservedTicket $ticket) => route('filament.app.pages.purchase', ['ticket' => $ticket->id])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No tickets purchased')
            ->emptyStateDescription($emptyDescription)
            ->emptyStateActions([
                TableAction::make('purchase')
                    ->label('Purchase Tickets')
                    ->url($url)
                    ->hidden(! $currentEvent),
            ]);
    }
}
