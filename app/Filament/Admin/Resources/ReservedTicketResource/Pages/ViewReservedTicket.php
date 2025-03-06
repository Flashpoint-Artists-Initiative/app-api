<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ReservedTicketResource\Pages;

use App\Filament\Admin\Resources\ReservedTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReservedTicket extends ViewRecord
{
    protected static string $resource = ReservedTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
