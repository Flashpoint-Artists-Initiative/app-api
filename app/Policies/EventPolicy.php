<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'events';

    /**
     * Allow unathenticated users to view all events
     *
     * Filtering for non-active events happens in the EventsController
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

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
