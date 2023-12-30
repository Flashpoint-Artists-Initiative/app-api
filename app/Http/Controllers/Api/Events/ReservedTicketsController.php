<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Models\TicketType;
use Orion\Http\Controllers\RelationController;

class ReservedTicketsController extends RelationController
{
    protected $model = TicketType::class;

    protected $relation = 'reservedTickets';

    public function includes(): array
    {
        return ['ticketType', 'user', 'event'];
    }
}
