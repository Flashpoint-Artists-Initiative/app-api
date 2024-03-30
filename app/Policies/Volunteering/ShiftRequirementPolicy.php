<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Models\User;
use App\Policies\AbstractModelPolicy;
use Illuminate\Database\Eloquent\Model;

class ShiftRequirementPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'requirements';

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return true;
    }
}
