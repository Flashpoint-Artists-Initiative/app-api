<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Orion\Concerns\DisableAuthorization;
use Orion\Http\Controllers\Controller;

class UsersController extends Controller
{
    use DisableAuthorization;

    protected $model = User::class;

    protected $resource = UserResource::class;
}
