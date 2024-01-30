<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\TicketTransferRequest;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class TicketsController extends Controller
{
    public function transferAction(TicketTransferRequest $request): JsonResponse
    {
        $transferUser = User::where('email', $request->email)->firstOrFail();
        PurchasedTicket::findMany($request->purchased_tickets)->each(function (PurchasedTicket $ticket) use ($transferUser) {
            $ticket->user_id = $transferUser->id;
            $ticket->save();
        });

        ReservedTicket::findMany($request->purchased_tickets)->each(function (ReservedTicket $ticket) use ($transferUser) {
            $ticket->user_id = $transferUser->id;
            $ticket->save();
        });

        return response()->json(status: 204);
    }
}
