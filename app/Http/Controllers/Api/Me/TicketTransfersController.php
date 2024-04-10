<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\OrionController;
use App\Http\Requests\TicketTransferCreateRequest;
use App\Http\Resources\TicketTransferResource;
use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use App\Policies\MeTicketTransferPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Route;
use Orion\Http\Requests\Request;

/**
 * @tags Me/TicketTransfers
 */
class TicketTransfersController extends OrionController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware(['lockdown:ticket'])->only('delete');
    }

    protected $model = TicketTransfer::class;

    protected $policy = MeTicketTransferPolicy::class;

    /**
     * @return string[]
     */
    public function alwaysIncludes(): array
    {
        return ['purchasedTickets', 'reservedTickets'];
    }

    /**
     * @param  string[]  $requestedRelations
     * @return Builder<TicketTransfer>
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        $route = $request->route();
        $user = auth()->user();

        abort_unless($route instanceof Route, 400, 'Invalid Request');
        abort_unless($user instanceof User, 400, 'Invalid Request');

        $method = $route->getActionMethod();

        if ($method === 'received') {
            $query->where('recipient_user_id', $user->id)
                ->orWhere(function (Builder $innerQuery) use ($user) {
                    $innerQuery->whereNull('recipient_user_id')
                        ->where('recipient_email', $user->email);
                });
        } else {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Initiate a ticket transfer
     */
    public function transferAction(TicketTransferCreateRequest $request): TicketTransferResource
    {
        $transfer = TicketTransfer::createTransfer($request->getTransferUser()->id, $request->email, $request->purchased_tickets, $request->reserved_tickets);

        return new TicketTransferResource($transfer);
    }

    /**
     * Complete a ticket transfer
     */
    public function completeAction(Request $request, TicketTransfer $ticketTransfer): JsonResponse
    {
        $this->authorize('complete', [$ticketTransfer]);

        $ticketTransfer->complete();

        return response()->json(status: 204);
    }

    /**
     * Get received transfers
     */
    public function received(Request $request): mixed
    {
        return parent::index($request);
    }
}
