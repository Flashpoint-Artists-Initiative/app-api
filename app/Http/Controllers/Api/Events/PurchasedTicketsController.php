<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Models\TicketType;
use Orion\Http\Controllers\RelationController;

class PurchasedTicketsController extends RelationController
{
    protected $model = TicketType::class;

    protected $relation = 'purchasedTickets';

    public function includes(): array
    {
        return ['ticketType', 'user', 'event'];
    }
}
