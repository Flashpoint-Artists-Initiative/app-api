<?php

declare(strict_types=1);

namespace App\Policies\Grants;

use App\Enums\ArtProjectStatus;
use App\Models\Grants\ArtProject;
use App\Models\User;
use App\Policies\AbstractModelPolicy;

class ArtProjectPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'artProjects';

    /**
     * Allow unauthenticated users to view approved art projects with active events
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * @param  ArtProject  $artProject
     */
    public function view(?User $user, $artProject): bool
    {
        if ($artProject->project_status === 'approved'
            && $artProject->event->active
            && $artProject->project_status === ArtProjectStatus::Approved->value
        ) {
            return true;
        }

        if ($user?->id === $artProject->user_id) {
            return true;
        }

        if ($user?->can('artProjects.viewPending')) {
            return true;
        }

        return false;
    }

    /**
     * @param  ArtProject  $artProject
     */
    public function update(User $user, $artProject): bool
    {
        return $user->id === $artProject->user_id || $user->can('artProjects.update');
    }

    /**
     * @param  ArtProject  $artProject
     */
    public function delete(User $user, $artProject): bool
    {
        return $user->id === $artProject->user_id || $user->can('artProjects.delete');
    }
}
