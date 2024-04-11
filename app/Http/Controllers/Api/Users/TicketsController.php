<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Support\Collection;

class TicketsController extends Controller
{
    /**
     * @return array{data: array{purchasedTickets: Collection<int, PurchasedTicket>, reservedTickets: Collection<int, ReservedTicket>}}
     */
    public function indexAction(User $user): array
    {
        $this->authorize('view', $user);

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
