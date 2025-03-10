<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Ticketing\Order;
use App\Models\Ticketing\PurchasedTicket;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class OrderTicketsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    public Order $record;

    public function render(): string
    {
        return <<<'HTML'
        <div class="grid flex-1 auto-cols-fr gap-y-2">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Items</span>
            {{  $this->table }}
        </div>
        HTML;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(PurchasedTicket::query()->where('order_id', $this->record->id))
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('ticketType.name')
                    ->description(fn (PurchasedTicket $record) => $record->reservedTicket?->note),
                TextColumn::make('ticketType.price')
                    ->label('Cost')
                    ->money('USD', 100),
            ])
            ->paginated(false);
    }
}
