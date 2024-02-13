<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class MeTicketTransferPolicy extends TicketTransferPolicy
{
    protected string $prefix = 'ticketTransfers';

    public function viewAny(User $user): bool
    {
        return true;
    }
}
