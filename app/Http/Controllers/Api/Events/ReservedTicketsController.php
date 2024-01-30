<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\TicketTransferRequest;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReservedTicketsController extends OrionRelationsController
{
    protected $model = TicketType::class;

    protected $relation = 'reservedTickets';

    public function includes(): array
    {
        return ['ticketType', 'user', 'event', 'purchasedTicket'];
    }

    public function transferAction(TicketTransferRequest $request, TicketType $ticketType, ReservedTicket $reservedTicket): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->id == $reservedTicket->user_id) {
            return response()->json(['error' => 'Attempted to transfer ticket to current owner.'], 422);
        }

        $reservedTicket->user_id = $user->id;

        if (! $reservedTicket->save()) {
            return response()->json(['error' => 'Cannot transfer reserved ticket that has been purchased.'], 422);
        }

        return response()->json(status: 204);
    }
}
