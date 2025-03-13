<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WaiverResource\Pages;

use App\Filament\Admin\Resources\WaiverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaiver extends EditRecord
{
    protected static string $resource = WaiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
