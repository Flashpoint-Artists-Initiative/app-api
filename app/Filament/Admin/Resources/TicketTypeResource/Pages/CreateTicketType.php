<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources\TicketTypeResource\Pages;

use App\Filament\Admin\Resources\TicketTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketType extends CreateRecord
{
    protected static string $resource = TicketTypeResource::class;
}
