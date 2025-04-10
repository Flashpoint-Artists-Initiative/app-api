<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ArtProjectResource\Pages;

use App\Filament\Admin\Resources\ArtProjectResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateArtProject extends CreateRecord
{
    protected static string $resource = ArtProjectResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] = Event::getCurrentEventId();

        return $data;
    }
}
