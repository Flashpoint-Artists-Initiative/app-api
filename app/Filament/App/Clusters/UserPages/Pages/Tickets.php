<?php

declare(strict_types=1);

namespace App\Filament\App\Clusters\UserPages\Pages;

use App\Filament\App\Clusters\UserPages;
use App\Livewire\PurchasedTicketsTable;
use App\Livewire\ReservedTicketsTable;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Tickets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'My Tickets';

    protected static string $view = 'filament.app.clusters.user-pages.pages.tickets';

    protected static ?string $cluster = UserPages::class;

    public function ticketsInfolist(Infolist $infolist): Infolist
    {
        /** @var User */
        $user = Auth::user();

        return $infolist
            ->schema([
                Livewire::make(PurchasedTicketsTable::class)->key('purchased-tickets-table'),
                Livewire::make(ReservedTicketsTable::class)->key('reserved-tickets-table')
                    ->visible(fn() => $user->availableReservedTickets()->exists()),
            ])
            ->state([
                'name' => 'John Doe',
                'email' => 'joe@example.com',
            ]);
    }
}
