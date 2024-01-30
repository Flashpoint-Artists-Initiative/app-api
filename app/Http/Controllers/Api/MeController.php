<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MeRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class MeController extends Controller
{
    public static function includes(): array
    {
        return [
            'reservedTickets',
            'purchasedTickets',
            'orders',
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
        $user->load(['reservedTickets', 'purchasedTickets']);

        return $user;
    }

    public function ordersAction()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->load('orders');

        return $user;
    }
}
