<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

abstract class AbstractModelPolicy
{
    protected string $prefix;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can("{$this->prefix}.viewAny");
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model): bool
    {
        return $user->can("{$this->prefix}.view");
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can("{$this->prefix}.create");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model): bool
    {
        return $user->can("{$this->prefix}.update");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model): bool
    {
        return $user->can("{$this->prefix}.delete");
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, $model): bool
    {
        return $user->can("{$this->prefix}.restore");
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, $model): bool
    {
        return $user->can("{$this->prefix}.forceDelete");
    }
}
