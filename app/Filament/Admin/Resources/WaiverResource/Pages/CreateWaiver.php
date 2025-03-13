<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WaiverResource\Pages;

use App\Filament\Admin\Resources\WaiverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWaiver extends CreateRecord
{
    protected static string $resource = WaiverResource::class;
}
