<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartCreateRequest;
use App\Models\Ticketing\Cart;
use App\Models\User;
use Illuminate\Support\Collection;

class CartController extends Controller
{
    protected static bool $cartWasDeleted = false;

    /**
     * Return a user's cart
     */
    public function indexAction()
    {
        $activeCart = $this->getActiveCart();

        if (! $activeCart) {
            if (static::$cartWasDeleted) {
                return response()->json(['message' => 'Your cart expired'], 404);
            }

            return response()->json(['message' => 'No cart found'], 404);
        }

        return response()->json(['data' => $activeCart]);
    }

    public function createAction(CartCreateRequest $request)
    {
        $user = auth()->user();

        /**
         * Delete existing carts
         *
         * Do deletes this way so the 'deleting' event fires
         * There *shouldn't* ever be more than one cart for a user, so this shouldn't be expensive
         */
        Cart::where('user_id', $user->id)->get()->each->delete();

        $cart = Cart::create(['user_id' => auth()->user()->id]);
        $cart->fillItems($request->validated('tickets'));
        $cart->load('items');

        return response()->json(['data' => $cart], 201);
    }

    public function deleteAction()
    {
        $cart = $this->getActiveCart();

        if ($cart) {
            $cart->delete();

            return response()->json(status: 204);
        }

        return response()->json(status: 404);
    }

    /**
     * Gets the active cart for the logged in user
     *
     * Deletes any expired or extra carts
     */
    protected function getActiveCart(): ?Cart
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var Collection<int, Cart> $carts */
        $carts = Cart::where('user_id', $user->id)->with('items')->orderBy('expiration_date', 'asc')->get();

        // We should never have more than one cart per user, but just in case...
        // Delete all expired carts, and any older than the newest.
        return $carts->filter(function (Cart $c, int $k) {
            if ($c->isExpired() || $k > 0) {
                $c->delete();
                static::$cartWasDeleted = true;
            }

            return $c->exists;
        })->first();
    }
}
