<?php

declare(strict_types=1);

namespace App\Policies;

class PurchasedTicketPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'purchasedTickets';
}
