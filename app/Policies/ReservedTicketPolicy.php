<?php

declare(strict_types=1);

namespace App\Policies;

class ReservedTicketPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'reservedTickets';
}
