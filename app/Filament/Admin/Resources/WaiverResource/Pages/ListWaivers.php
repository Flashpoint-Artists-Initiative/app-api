<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\WaiverResource\Pages;

use App\Filament\Admin\Resources\WaiverResource;
use App\Models\Event;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaivers extends ListRecords
{
    protected static string $resource = WaiverResource::class;

    public function getTitle(): string
    {
        $event = Event::getCurrentEvent()->name ?? 'Unknown Event';
        return "$event - Waivers";
    }
    
    // @phpstan-ignore-next-line Required by parent class
    protected $listeners = [
        'active-event-updated' => '$refresh',
    ];

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
