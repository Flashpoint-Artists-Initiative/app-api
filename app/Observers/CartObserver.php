<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ticketing\Cart;
use App\Services\StripeService;

class CartObserver
{
    public function __construct(protected StripeService $stripeService)
    {

    }

    public function creating(Cart $cart): bool
    {
        // Don't allow a cart to be created for a user if one already exists
        $cart->expiration_date = now()->addMinutes(config('app.cart_expiration_minutes'));

        if (Cart::where('user_id', $cart->user_id)->exists()) {
            return false;
        }

        return true;
    }

    public function updating(Cart $cart): bool
    {
        // Don't allow a cart to be updated
        return false;
    }

    /**
     * Handle the Cart "deleted" event.
     */
    public function deleted(Cart $cart): void
    {
        $cart->items()->delete();
        $this->stripeService->expireCheckoutFromCart($cart);
    }

    /**
     * Handle the Cart "restored" event.
     */
    public function restored(Cart $cart): void
    {
        //
    }

    /**
     * Handle the Cart "force deleted" event.
     */
    public function forceDeleted(Cart $cart): void
    {
        //
    }
}
