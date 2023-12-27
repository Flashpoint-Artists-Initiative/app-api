<?php

declare(strict_types=1);

namespace App\Policies;

class EventPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'events';
}
