<?php

declare(strict_types=1);

namespace App\Policies;

class UserPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'users';
}
