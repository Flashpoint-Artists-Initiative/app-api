<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Routing\Route;

class TicketTransferCreateWithUserRequest extends TicketTransferCreateRequest
{
    public function getTransferUser(): User
    {
        /** @var Route */
        $route = $this->route();

        /** @var User */
        return $route->parameter('user');
    }
}
