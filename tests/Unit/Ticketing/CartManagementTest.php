<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    #[Test]
    public function cart_user_relation(): void
    {
        $user = User::firstOrFail();
        $cart = Cart::create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $cart->user->id);
    }

    #[Test]
    public function cart_update_fails(): void
    {
        $user = User::firstOrFail();
        $cart = Cart::create(['user_id' => $user->id]);

        $cart->stripe_checkout_id = 'whatever';
        $this->assertFalse($cart->save());
    }

    #[Test]
    public function cart_item_update_fails(): void
    {
        $user = User::firstOrFail();
        $cart = Cart::create(['user_id' => $user->id]);
        $cartItem = CartItem::create(['cart_id' => $cart->id, 'ticket_type_id' => 1, 'quantity' => 1]);

        $cartItem->quantity = 2;
        $this->assertFalse($cartItem->save());
    }
}
