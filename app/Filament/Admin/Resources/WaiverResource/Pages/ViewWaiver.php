<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WaiverResource\Pages;

use App\Filament\Admin\Resources\WaiverResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWaiver extends ViewRecord
{
    protected static string $resource = WaiverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
