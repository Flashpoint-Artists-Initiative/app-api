<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Ticketing\TicketType;

class ReservedTicketsController extends OrionRelationsController
{
    protected $model = TicketType::class;

    protected $relation = 'reservedTickets';

    public function includes(): array
    {
        return ['ticketType', 'user', 'event', 'purchasedTicket'];
    }
}
