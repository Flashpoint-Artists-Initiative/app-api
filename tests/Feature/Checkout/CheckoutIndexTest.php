<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Database\Seeders\Testing\EventSeeder;
use Tests\ApiRouteTestCase;

class CheckoutIndexTest extends ApiRouteTestCase
{
    public string $routeName = 'api.checkout.index';

    public bool $seed = true;

    public string $seeder = EventSeeder::class;

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
        $this->createCart($user);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_cart_index_call_with_multiple_carts_returns_single_cart(): void
    {
        $user = User::first();
        $this->createCart($user);
        $this->createCart($user);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_cart_index_call_with_multiple_unexpired_carts_returns_success(): void
    {
        $user = User::doesntHave('carts')->first();

        $this->createCart($user);
        $cart = $user->carts()->first();
        $this->travel(1)->minute();

        $secondCart = Cart::create(['user_id' => $user->id]);
        $secondCart->stripe_checkout_id = $cart->stripe_checkout_id;
        $secondCart->saveQuietly();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);

        $cartCount = $user->carts()->count();
        $unexpiredCartCount = $user->carts()->notExpired()->count();

        $this->assertEquals(2, $cartCount);
        $this->assertEquals(1, $unexpiredCartCount);
    }

    public function test_cart_index_call_with_expired_cart_returns_error(): void
    {
        $user = User::first();
        $this->createCart($user);

        $this->travel(1)->hour();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }

    protected function createCart(User $user): void
    {
        $ticketType = TicketType::query()->available()->first();
        $this->actingAs($user)->postJson(route('api.checkout.store'), [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);
    }
}
