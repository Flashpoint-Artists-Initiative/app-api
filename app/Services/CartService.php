<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

class CartService
{
    public function __construct(protected StripeService $stripeService)
    {
    }

    protected static bool $cartWasExpired = false;

    /**
     * Gets the active cart for the logged in user
     *
     * Deletes any expired or extra carts
     */
    public function getActiveCart(?User $user = null): ?Cart
    {
        $carts = $this->getAllUnexpiredCarts($user);

        // A User can only have one active cart at a time
        $carts = $this->expireExtraCarts($carts);
        $this->assertSingleCart($carts);

        return $carts->first();
    }

    public function getAllUnexpiredCarts(?User $user = null): Collection
    {
        $user = $this->ensureUser($user);

        return Cart::where('user_id', $user->id)->notExpired()->orderBy('expiration_date', 'asc')->get();
    }

    public function expireAllUnexpiredCarts(?User $user = null): void
    {
        $user = $this->ensureUser($user);

        Cart::where('user_id', $user->id)->notExpired()->get()->each(function (Cart $cart) {
            $cart->expire();
            $this->stripeService->expireCheckoutFromCart($cart);
        });
    }

    protected function ensureUser(?User $user = null): User
    {
        return $user ?? auth()->user();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function assertSingleCart(Collection $carts): void
    {
        if ($carts->count() > 1) {
            throw new Exception('User has multiple carts');
        }
    }

    public function createGeneralSaleCartAndItems(array $tickets): Cart
    {
        $cart = Cart::create(['user_id' => auth()->user()->id]);

        foreach ($tickets as $row) {
            CartItem::create([
                'cart_id' => $cart->id,
                'ticket_type_id' => $row['id'],
                'quantity' => $row['quantity'],
            ]);
        }

        return $cart;
    }

    public function createReservedCartAndItems(array $tickets): Cart
    {
        $cart = Cart::create(['user_id' => auth()->user()->id]);
        $reservedTickets = auth()->user()->reservedTickets;

        foreach ($tickets as $reservedId) {
            if ($reservedTicket = $reservedTickets->find($reservedId)) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'ticket_type_id' => $reservedTicket->ticket_type_id,
                    'reserved_ticket_id' => $reservedTicket->id,
                    'quantity' => 1,
                ]);
            }
        }

        return $cart;
    }

    /**
     * Expire all carts except the newest.
     */
    public function expireExtraCarts(Collection $carts): Collection
    {
        return $carts->filter(function (Cart $c, int $k) {
            if (! $c->is_expired && $k > 0) {
                $c->expire();
                static::$cartWasExpired = true;
            }

            return ! $c->is_expired;
        });
    }

    public function cartWasExpired(): bool
    {
        return static::$cartWasExpired;
    }

    public function assertSessionHasCart(string $id): void
    {
        abort_unless(Cart::query()->stripeCheckoutId($id)->exists(), 422, 'No cart found for the session_id');
    }

    public function resolveCompletedCart(string $sessionId, int $orderId): void
    {
        $this->assertSessionHasCart($sessionId);

        $cart = Cart::query()->stripeCheckoutId($sessionId)->firstOrFail();

        $cart->items->each(fn ($item) => PurchasedTicket::createFromCartItem($item, $cart->user_id, $orderId));
    }
}
