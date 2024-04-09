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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MeController extends Controller
{
    /**
     * @return string[]
     */
    public static function includes(): array
    {
        return [
            'reservedTickets',
            'purchasedTickets',
            'orders',
            'waivers',
            'ticketTransfers',
        ];
    }

    /**
     * Get the currently logged in user
     *
     * Always returns the user's roles and permissions.  Optionally returns other relations
     */
    public function indexAction(MeRequest $request): UserResource
    {
        $includes = array_merge($request->input('include', []), ['roles', 'permissions']);
        /** @var User $user */
        $user = auth()->user();
        $user->load($includes);

        return new UserResource($user);
    }

    /**
     * Update the logged in user
     */
    public function update(UserRequest $request): UserResource
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * Get the logged in user's tickets
     *
     * Returns all purchased and reserved tickets
     *
     * @return array{data: array{purchasedTickets: Collection<int, PurchasedTicket>, reservedTickets: Collection<int, ReservedTicket>}}
     */
    public function ticketsAction(): array
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

    /**
     * Get the logged in user's orders
     */
    public function ordersAction(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)->paginate();

        return OrderResource::collection($orders);
    }

    /**
     * Get the logged in user's waivers
     */
    public function waiversAction(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth()->user();
        $waivers = CompletedWaiver::where('user_id', $user->id)->paginate();

        return WaiverResource::collection($waivers);
    }
}
