<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;

class TicketsController extends Controller
{
    public function indexAction(User $user)
    {
        $this->authorize('user.ticket-transfer', $user);

        $reservedTickets = ReservedTicket::where('user_id', $user->id)->get();
        $purchasedTickets = PurchasedTicket::where('user_id', $user->id)->get();

        return [
            'data' => [
                'purchasedTickets' => $purchasedTickets,
                'reservedTickets' => $reservedTickets,
            ],
        ];
    }
}
