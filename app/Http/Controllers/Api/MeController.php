<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WaiverResource;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Order;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;

class MeController extends Controller
{
    public static function includes(): array
    {
        return [
            'reservedTickets',
            'purchasedTickets',
            'orders',
            'waivers',
        ];
    }

    public function indexAction(MeRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $user->load($request->input('include', []));

        return new UserResource($user);
    }

    // Going against naming convention here to use the existing UserRequest
    public function update(UserRequest $request)
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    public function ticketsAction()
    {
        /** @var User $user */
        $user = auth()->user();
        $reservedTickets = ReservedTicket::where('user_id', $user->id)->get();
        $purchasedTickets = PurchasedTicket::where('user_id', $user->id)->get();

        return [
            'data' => [
                'purchasedTickets' => $purchasedTickets,
                'reservedTickets' => $reservedTickets,
            ],
        ];
    }

    public function ordersAction()
    {
        /** @var User $user */
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)->paginate();

        return OrderResource::collection($orders);
    }

    public function waiversAction()
    {
        /** @var User $user */
        $user = auth()->user();
        $waivers = CompletedWaiver::where('user_id', $user->id)->paginate();

        return WaiverResource::collection($waivers);
    }
}
