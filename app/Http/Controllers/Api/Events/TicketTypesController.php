<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Models\Event;
use Orion\Http\Controllers\RelationController;

class TicketTypesController extends RelationController
{
    protected $model = Event::class;

    protected $relation = 'ticketTypes';

    public function includes(): array
    {
        return ['event', 'purchasedTickets', 'reservedTickets'];
    }
}
