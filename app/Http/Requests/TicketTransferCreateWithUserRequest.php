<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;

class TicketTransferCreateWithUserRequest extends TicketTransferCreateRequest
{
    public function getTransferUser(): User
    {
        return $this->route()->parameter('user');
    }
}
