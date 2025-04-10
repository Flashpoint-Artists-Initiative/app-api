<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ArtProjectStatusEnum;
use App\Models\Grants\ArtProject;
use App\Models\User;
use App\Policies\AbstractModelPolicy;

class ArtProjectPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'artProjects';

    /**
     * @param  ArtProject  $artProject
     */
    public function view(?User $user, $artProject): bool
    {
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
