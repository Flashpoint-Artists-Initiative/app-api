<?php

declare(strict_types=1);

namespace App\Filament\App\Clusters\UserPages\Pages;

use App\Filament\App\Clusters\UserPages;
use App\Livewire\PurchasedTicketsTable;
use App\Livewire\ReservedTicketsTable;
use App\Models\Ticketing\Order;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Orders extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.app.clusters.user-pages.pages.orders';

    protected static ?string $cluster = UserPages::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->currentUser())
            ->columns([
                TextColumn::make('id')
                    ->label('Order Number')
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Purchase Date')
                    ->dateTime('F jS, Y g:i A T', 'America/New_York'),
                TextColumn::make('amount_total')
                    ->label('Total')
                    ->money('usd', 100)
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Details')
                    ->icon('heroicon-o-eye')
                    ->modalContent(fn(Order $record) => view('filament.app.clusters.user-pages.modals.orders-modal', [
                        'order' => $record,
                    ]))
                    ->modalCancelAction(false)
                    ->modalSubmitActionLabel('Close')
            ]);
    }
}
