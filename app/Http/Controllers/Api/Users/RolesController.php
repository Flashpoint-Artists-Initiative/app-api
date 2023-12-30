<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Models\User;
use App\Policies\UserRolesPolicy;
use Orion\Http\Controllers\RelationController;

class RolesController extends RelationController
{
    protected $model = User::class;

    protected $relation = 'roles';

    protected $policy = UserRolesPolicy::class;
}
