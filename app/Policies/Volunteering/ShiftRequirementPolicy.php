<?php

declare(strict_types=1);

namespace App\Policies\Volunteering;

use App\Policies\AbstractModelPolicy;

class ShiftRequirementPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'shiftRequirements';
}
