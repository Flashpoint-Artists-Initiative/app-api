<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\ReservedTicketResource\Pages;

use App\Filament\Admin\Resources\ReservedTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReservedTicket extends CreateRecord
{
    protected static string $resource = ReservedTicketResource::class;

    // @phpstan-ignore-next-line Required by parent class
    protected $listeners = [
        'active-event-updated' => '$refresh',
    ];
}
