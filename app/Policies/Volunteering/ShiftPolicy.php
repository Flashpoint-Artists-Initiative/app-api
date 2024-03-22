<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Policies\AbstractModelPolicy;
use Illuminate\Database\Eloquent\Model;

class ShiftPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'shifts';

    /**
     * Allow all users to view all shifts
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * @param  Shift  $model
     */
    public function view(User $user, Model $model): bool
    {
        if ($model->team->active && $model->team->event->active) {
            return true;
        }

        return parent::view($user, $model);
    }
}
