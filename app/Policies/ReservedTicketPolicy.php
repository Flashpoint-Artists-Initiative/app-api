<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class ReservedTicketPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'reservedTickets';

    public function update(User $user, $model): bool
    {
        if ($model->is_purchased) {
            return false;
        }

        return parent::update($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        if ($model->is_purchased) {
            return false;
        }

        return parent::delete($user, $model);
    }
}
