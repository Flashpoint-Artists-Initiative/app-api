<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Ticketing\Order;
use App\Services\StripeService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class OrderInfolist extends Component implements HasInfolists, HasForms
{
    use InteractsWithInfolists, InteractsWithForms;

    public int $orderId;

    public function orderInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(Order::find($this->orderId))
            ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('id')
                                ->label('Order Number')
                                ->prefix('#'),
                            TextEntry::make('created_at')
                                ->label('Purchase Date')
                                ->dateTime('F jS, Y g:i A T', 'America/New_York'),
                            TextEntry::make('event.name')
                                ->label('Event'),
                        ]),
                    Livewire::make(OrderTicketsTable::class),
                    Fieldset::make('Order Summary')
                        ->schema([
                            TextEntry::make('amount_subtotal')
                                ->label('Subtotal')
                                ->money('USD', 100),
                            TextEntry::make('amount_tax')
                                ->label('Sales Tax')
                                ->money('USD', 100),
                            TextEntry::make('amount_fees')
                                ->label('Fees')
                                ->money('USD', 100),
                            TextEntry::make('amount_total')
                                ->label('Total')
                                ->money('USD', 100),
                        ])->columns(4),
            ])
            ->columns(1);
    }
    
    // public function mount(int $order): void
    // {
    //     $this->order = Order::find($order);
    // }

    public function render(): string
    {
        return <<<'HTML'
        <div>
            {{ $this->orderInfolist }}
        </div>
        HTML;
    }
}
