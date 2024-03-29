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

/**
 * @tags Me/TicketTransfers
 */
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

        $method = $request->route()->getActionMethod();

        if ($method === 'received') {
            $query->where('recipient_user_id', auth()->user()->id)
                ->orWhere(function (Builder $innerQuery) {
                    $innerQuery->whereNull('recipient_user_id')
                        ->where('recipient_email', auth()->user()->email);
                });
        } else {
            $query->where('user_id', auth()->user()->id);
        }

        return $query;
    }

    /**
     * Initiate a ticket transfer
     */
    public function transferAction(TicketTransferCreateRequest $request)
    {
        $transfer = TicketTransfer::createTransfer($request->getTransferUser()->id, $request->email, $request->purchased_tickets, $request->reserved_tickets);

        return new TicketTransferResource($transfer);
    }

    /**
     * Complete a ticket transfer
     */
    public function completeAction(Request $request, TicketTransfer $ticketTransfer)
    {
        $this->authorize('complete', [$ticketTransfer]);

        $ticketTransfer->complete();

        return response()->json(status: 204);
    }

    /**
     * Get received transfers
     */
    public function received(Request $request)
    {
        return parent::index($request);
    }
}
