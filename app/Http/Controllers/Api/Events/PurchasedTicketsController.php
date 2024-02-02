<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\TicketTransferRequest;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Orion\Http\Requests\Request;

class PurchasedTicketsController extends OrionRelationsController
{
    protected $model = TicketType::class;

    protected $relation = 'purchasedTickets';

    public function includes(): array
    {
        return ['ticketType', 'user', 'event', 'reservedTicket'];
    }

    protected function buildIndexFetchQuery(Request $request, Model $event, array $requestedRelations): Relation
    {
        $relation = parent::buildIndexFetchQuery($request, $event, $requestedRelations);
        /** @var ?User $user */
        $user = auth()->user();

        // Hide tickets that don't belong to the user if they don't have permission to view all of them
        if (! $user || ! $user->can('purchasedTickets.viewAny')) {
            $relation->getQuery()->where('user_id', $user->id);
        }

        return $relation;
    }

    public function transferAction(TicketTransferRequest $request, TicketType $ticketType, PurchasedTicket $purchasedTicket): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $purchasedTicket->user_id = $user->id;
        $purchasedTicket->save();

        return response()->json(status: 204);
    }
}