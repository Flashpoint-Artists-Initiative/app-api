<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PurchasedTicketResource\Pages;

use App\Filament\Admin\Resources\PurchasedTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchasedTickets extends ListRecords
{
    protected static string $resource = PurchasedTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
