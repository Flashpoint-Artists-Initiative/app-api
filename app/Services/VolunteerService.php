<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Volunteering\Shift;

class VolunteerService
{
    public function userHasOverlappingShift(Shift $shift, ?User $user = null): Shift|false
    {
        /** @var User $user */
        $user = $user ?? auth()->user();
        $user->load('shifts');

        foreach ($user->shifts as $existingShift) {
            if ($existingShift->overlapsWith($shift)) {
                return $existingShift;
            }
        }

        return false;
    }
}
