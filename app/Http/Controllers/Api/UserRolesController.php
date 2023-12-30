<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrionRelationsController;
use App\Models\User;
use App\Policies\UserRolesPolicy;

class UserRolesController extends OrionRelationsController
{
    protected $model = User::class;

    protected $relation = 'roles';

    protected $policy = UserRolesPolicy::class;
}
