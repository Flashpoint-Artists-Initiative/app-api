<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\OrionRelationsController;
use App\Models\User;
use App\Policies\UserRolesPolicy;

class RolesController extends OrionRelationsController
{
    protected $model = User::class;

    protected $relation = 'roles';

    protected $policy = UserRolesPolicy::class;
}
