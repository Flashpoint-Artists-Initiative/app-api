<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\TicketTransferCreateWithUserRequest;
use App\Http\Resources\TicketTransferResource;
use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use App\Policies\TicketTransferPolicy;

class TicketTransfersController extends OrionRelationsController
{
    protected $model = User::class;

    protected $relation = 'ticketTransfers';

    protected $policy = TicketTransferPolicy::class;

    public function transferAction(TicketTransferCreateWithUserRequest $request, User $user)
    {
        $this->authorize('create', [TicketTransfer::class]);
        $transfer = TicketTransfer::createTransfer($user->id, $request->email, $request->purchased_tickets, $request->reserved_tickets);

        return new TicketTransferResource($transfer);
    }
}
