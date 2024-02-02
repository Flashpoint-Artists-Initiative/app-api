<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ticketing\Waiver;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class WaiverPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'waivers';

    /**
     * @param  Waiver  $waiver
     */
    public function update(User $user, Model $waiver): bool
    {
        // Waivers with any completions cannot be modified
        if ($waiver->completedWaivers()->exists()) {
            return false;
        }

        return parent::update($user, $waiver);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return true;
    }
}
