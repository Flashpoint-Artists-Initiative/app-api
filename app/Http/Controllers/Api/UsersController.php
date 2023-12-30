<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrionController;
use App\Http\Resources\UserResource;
use App\Models\User;

class UsersController extends OrionController
{
    protected $model = User::class;

    protected $resource = UserResource::class;

    public function includes(): array
    {
        return ['roles', 'permissions'];
    }
}
