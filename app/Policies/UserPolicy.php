<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'users';

    public function update(User $user, $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return parent::update($user, $model);
    }

    public function view(User $user, $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return parent::update($user, $model);
    }

    public function delete(User $user, $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return parent::update($user, $model);
    }
}
