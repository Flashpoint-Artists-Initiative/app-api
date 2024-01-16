<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\User;
use Tests\ApiRouteTestCase;

class CheckoutIndexTest extends ApiRouteTestCase
{
    public string $routeName = 'api.checkout.index';

    public bool $seed = true;

    public function test_cart_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_cart_index_call_without_cart_returns_error(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }

    public function test_cart_index_call_with_cart_returns_success(): void
    {
        $user = User::first();
        Cart::create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_cart_index_call_with_expired_cart_returns_error(): void
    {
        $user = User::first();
        Cart::create(['user_id' => $user->id]);

        $this->travel(1)->hour();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }
}
