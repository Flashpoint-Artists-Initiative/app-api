<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\TicketTypeResource\Pages;

use App\Filament\Admin\Resources\TicketTypeResource;
use App\Filament\Traits\HasParentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Event;

class CreateTicketType extends CreateRecord
{
    use HasParentResource;

    protected static string $resource = TicketTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? static::getParentResource()::getUrl('lessons.index', [
            'parent' => $this->parent,
        ]);
    }
 
    // This can be moved to Trait, but we are keeping it here
    //   to avoid confusion in case you mutate the data yourself
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the parent relationship key to the parent resource's ID.
        /** @var Event $parent */
        $parent = $this->parent;
        $data[$this->getParentRelationshipKey()] = $parent->id;
 
        return $data;
    }
}
