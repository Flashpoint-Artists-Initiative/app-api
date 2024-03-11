<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Models\User;
use App\Models\Volunteering\Team;
use App\Policies\AbstractModelPolicy;

class TeamPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'teams';

    /**
     * Allow unathenticated users to view all events
     *
     * Filtering for non-active events happens in the TeamsController
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * @param  Team  $team
     */
    public function view(?User $user, $team): bool
    {
        if ($team->active) {
            return true;
        }

        if ($user?->can('teams.viewPending')) {
            return true;
        }

        return false;
    }
}
