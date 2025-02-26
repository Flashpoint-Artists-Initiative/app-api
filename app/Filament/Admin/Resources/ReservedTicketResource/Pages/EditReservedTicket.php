<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\ReservedTicketResource\Pages;

use App\Filament\Admin\Resources\ReservedTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReservedTicket extends EditRecord
{
    protected static string $resource = ReservedTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
