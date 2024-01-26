<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\Order;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use App\Services\CartService;
use App\Services\StripeService;
use Database\Seeders\Testing\EventSeeder;
use Mockery\MockInterface;
use Stripe\Checkout\Session;
use Stripe\Event;
use Tests\ApiRouteTestCase;

class CheckoutCompleteTest extends ApiRouteTestCase
{
    public string $routeName = 'api.checkout.complete';

    public bool $seed = true;

    public string $seeder = EventSeeder::class;

    public Session $session;

    public function setUp(): void
    {
        parent::setUp();

        auth()->login(User::first());

        $data = json_decode(file_get_contents(storage_path('testing/stripe_checkout_completed_event.json')), true);

        $event = Event::constructFrom($data);
        /** @phpstan-ignore-next-line */
        $this->session = $event->data->object;

        /** @var CartService $cartService */
        $cartService = app()->make(CartService::class);

        $ticketType = TicketType::query()->available()->first();

        $cart = $cartService->createGeneralSaleCartAndItems([
            [
                'id' => $ticketType->id,
                'quantity' => 1,
            ],
        ]);

        $cart->setStripeCheckoutIdAndSave($this->session->id);

        $this->partialMock(StripeService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCheckoutSession')->andReturn($this->session);
        });

    }

    public function test_checkout_complete_call_not_logged_in_returns_error(): void
    {
        auth()->logout();

        $response = $this->post($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_checkout_complete_call_without_cart_returns_error(): void
    {
        $user = User::first();
        $carts = Cart::where('user_id', $user->id)->get();

        $this->assertCount(1, $carts);

        $carts->first()->deleteQuietly();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_checkout_complete_call_with_cart_returns_success(): void
    {
        $user = User::first();
        $purchasedTicketCount = $user->PurchasedTickets()->count();

        $this->assertEquals(0, $purchasedTicketCount);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(204);

        $this->assertEquals(1, User::first()->PurchasedTickets()->count());
    }

    public function test_checkout_complete_call_with_incorrect_checkout_id_returns_error(): void
    {
        $user = User::first();
        $cart = $user->activeCart;
        $cart->stripe_checkout_id = 'wrong';
        $cart->saveQuietly();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(404);
    }

    public function test_checkout_complete_call_with_incomplete_session_returns_error(): void
    {
        $user = User::first();

        $this->session->status = 'incomplete';

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_checkout_complete_call_with_unpaid_session_returns_error(): void
    {
        $user = User::first();

        $this->session->payment_status = 'unpaid';

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_checkout_complete_call_with_existing_order_returns_error(): void
    {
        $user = User::first();

        Order::create([
            'user_email' => $user->email,
            'user_id' => $user->id,
            'event_id' => 1,
            'cart_id' => 1,
            'amount_subtotal' => 1,
            'amount_total' => 1,
            'amount_tax' => 1,
            'quantity' => 1,
            'stripe_checkout_id' => $this->session->id,
            'ticket_data' => '{}',
        ]);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'session_id' => $this->session->id,
        ]);

        $response->assertStatus(422);
    }
}
