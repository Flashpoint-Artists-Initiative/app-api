<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\OrionController;
use App\Http\Requests\TicketTransferCreateRequest;
use App\Http\Resources\TicketTransferResource;
use App\Models\Ticketing\TicketTransfer;
use App\Policies\MeTicketTransferPolicy;
use Illuminate\Database\Eloquent\Builder;
use Orion\Http\Requests\Request;

class TicketTransfersController extends OrionController
{
    protected $model = TicketTransfer::class;

    protected $policy = MeTicketTransferPolicy::class;

    public function alwaysIncludes(): array
    {
        return ['purchasedTickets', 'reservedTickets'];
    }

    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);
        $query->where('user_id', auth()->user()->id);

        return $query;
    }

    public function transferAction(TicketTransferCreateRequest $request)
    {
        $transfer = TicketTransfer::createTransfer($request->getTransferUser()->id, $request->email, $request->purchased_tickets, $request->reserved_tickets);

        return new TicketTransferResource($transfer);
    }

    public function completeAction(Request $request, TicketTransfer $ticketTransfer)
    {
        $this->authorize('complete', [$ticketTransfer]);

        $ticketTransfer->complete();

        return response()->json(status: 204);
    }
}
