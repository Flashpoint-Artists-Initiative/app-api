<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutCreateRequest;
use App\Models\Ticketing\Cart;
use App\Models\User;
use Illuminate\Support\Collection;

class CheckoutController extends Controller
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
                return response()->json(['message' => 'Your checkout session expired'], 404);
            }

            return response()->json(['message' => 'No checkout session found'], 404);
        }

        $session = app('stripe')->checkout->sessions->retrieve($activeCart->stripe_checkout_id);

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
        ]]);
    }

    public function createAction(CheckoutCreateRequest $request)
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
        $session = app('stripe')->createCheckoutFromCart($cart);
        $cart->stripe_checkout_id = $session->id;
        $cart->saveQuietly();

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
        ]], 201);
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
        $carts = Cart::where('user_id', $user->id)->orderBy('expiration_date', 'asc')->get();

        // We should never have more than one cart per user, but just in case...
        // Delete all expired carts, and any older than the newest.
        return $carts->filter(function (Cart $c, int $k) {
            if ($c->is_expired || $k > 0) {
                $c->delete();
                static::$cartWasDeleted = true;
            }

            return $c->exists;
        })->first();
    }
}
