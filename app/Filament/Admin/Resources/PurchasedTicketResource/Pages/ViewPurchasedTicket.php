<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\PurchasedTicketResource\Pages;

use App\Filament\Admin\Resources\PurchasedTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchasedTicket extends ViewRecord
{
    protected static string $resource = PurchasedTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
