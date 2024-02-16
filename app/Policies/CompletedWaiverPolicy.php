<?php

declare(strict_types=1);

namespace App\Policies;

class CompletedWaiverPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'completedWaivers';
}
