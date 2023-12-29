<?php

declare(strict_types=1);

namespace App\Policies;

class UserRolesPolicy extends AbstractModelPolicy
{
    protected string $prefix = 'roles';
}
