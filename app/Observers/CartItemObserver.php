<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Ticketing\CartItem;

class CartItemObserver
{
    /**
     * Handle the CartItem "created" event.
     */
    public function creating(CartItem $cartItem): bool
    {
        if (! is_null($cartItem->reserved_ticket_id)) {
            $cartItem->quantity = 1;
        }

        return true;
    }

    /**
     * Handle the CartItem "updated" event.
     */
    public function updating(CartItem $cartItem): bool
    {
        return false;
    }
}
