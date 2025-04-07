<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\TicketTypeResource\Pages;

use App\Filament\Admin\Resources\TicketTypeResource;
use App\Models\Event;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketType extends CreateRecord
{
    protected static string $resource = TicketTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['event_id'] = Event::getCurrentEventId();

        return $data;
    }
}
