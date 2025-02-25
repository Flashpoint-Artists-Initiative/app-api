<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'events';

    /**
     * Pass viewAny back to the parent policy.
     * This is to make Filament happy.
     * TODO: Figure out how this affects API access
     */

    /**
     * @param  Event  $event
     */
    public function view(?User $user, $event): bool
    {
        if ($event->active) {
            return true;
        }

        if ($user?->can('events.viewPending')) {
            return true;
        }

        return false;
    }
}
