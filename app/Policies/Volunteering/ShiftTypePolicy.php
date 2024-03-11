<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Models\User;
use App\Policies\AbstractModelPolicy;

class ShiftTypePolicy extends AbstractModelPolicy
{
    protected string $prefix = 'shiftTypes';

    /**
     * Allow unathenticated users to view all events
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }
}
