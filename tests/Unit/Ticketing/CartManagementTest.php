<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\Cart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    /**
     * A basic unit test example.
     */
    public function test_cart_create_twice_fails(): void
    {
        $user = User::first();
        Cart::create(['user_id' => $user->id]);

        $this->assertCount(1, Cart::all());

        Cart::create(['user_id' => $user->id]);

        $this->assertCount(1, Cart::all());
    }

    public function test_cart_user_relation(): void
    {
        $user = User::first();
        Cart::create(['user_id' => $user->id]);

        $cart = Cart::first();
        $this->assertEquals($user->id, $cart->user->id);
    }
}
