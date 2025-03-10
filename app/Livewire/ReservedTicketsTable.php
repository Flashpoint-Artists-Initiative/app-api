<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Ticketing\ReservedTicket;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class ReservedTicketsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    // @phpstan-ignore-next-line Required by parent class
    protected $listeners = [
        'active-event-updated' => '$refresh',
    ];

    public function render(): string
    {
        return <<<'HTML'
        <div class="grid flex-1 auto-cols-fr gap-y-8">
            <span class="text-3xl font-semibold">Your Reserved Tickets</span>
            {{  $this->table }}
        </div>
        HTML;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(ReservedTicket::query()->currentUser()->currentEvent()->canBePurchased())
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
            ->paginated(false);
    }
}
