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
        return ['roles', 'permissions', 'purchasedTickets', 'purchasedTickets.ticketType', 'reservedTickets', 'reservedTickets.ticketType'];
    }

    public function filterableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at'];
    }

    public function sortableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at'];
    }

    public function searchableBy(): array
    {
        return ['legal_name', 'preferred_name', 'display_name', 'email', 'birthday'];
    }
}
