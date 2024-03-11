<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Models\User;
use App\Policies\AbstractModelPolicy;

class ShiftPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'shifts';

    /**
     * Allow unathenticated users to view all events
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }
}
