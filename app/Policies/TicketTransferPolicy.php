<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TicketTransferPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'ticketTransfers';

    public function update(User $user, Model $model): bool
    {
        return false;
    }

    public function delete(User $user, Model $model): bool
    {
        /** @var TicketTransfer $model */
        if ($model->completed) {
            return false;
        }

        return parent::delete($user, $model);
    }
}
