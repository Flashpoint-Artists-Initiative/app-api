<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class CheckoutCreateReservedTest extends ApiRouteTestCase
{
    public string $routeName = 'api.checkout.store';

    public bool $seed = true;

    #[Test]
    public function cart_create_reserved_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'reserved' => [
                1,
            ],
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function cart_create_reserved_call_with_valid_data_returns_success(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $user->availableReservedTickets->firstOrFail()->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function cart_create_reserved_call_with_valid_data_twice_returns_new_cart(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->firstOrFail();
        $cartCount = Cart::count();
        $cartItemCount = CartItem::count();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $user->availableReservedTickets->firstOrFail()->id,
            ],
        ]);

        $response->assertStatus(201);

        $secondResponse = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $user->availableReservedTickets->firstOrFail()->id,
            ],
        ]);

        $secondResponse->assertStatus(201);

        $this->assertNotEquals($response->decodeResponseJson()->json('data.clientSecret'), $secondResponse->decodeResponseJson()->json('data.clientSecret'));

        $this->assertCount($cartCount + 2, Cart::all());
        $this->assertCount($cartItemCount + 2, CartItem::all());
    }

    #[Test]
    public function cart_create_reserved_call_with_expiration_date_returns_success(): void
    {
        $user = User::doesntHave('roles')->doesntHave('reservedTickets')->firstOrFail();

        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->active()->firstOrFail();
        $notOnSaleReservedTicketWithExpiration = ReservedTicket::create([
            'user_id' => $user->id,
            'expiration_date' => now()->addMinute(),
            'ticket_type_id' => $notOnSaleTicketType->id,
        ]);

        // Not on sale ticket type, with set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $notOnSaleReservedTicketWithExpiration->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function cart_create_reserved_call_without_expiration_date_and_not_on_sale_type_returns_failure(): void
    {
        $user = User::doesntHave('roles')->doesntHave('reservedTickets')->firstOrFail();

        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->active()->firstOrFail();
        $notOnSaleReservedTicketWithExpiration = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $notOnSaleTicketType->id,
        ]);

        // Not on sale ticket type, with set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $notOnSaleReservedTicketWithExpiration->id,
            ],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function cart_create_reserved_call_without_expiration_date_and_on_sale_type_returns_success(): void
    {
        $user = User::doesntHave('roles')->doesntHave('reservedTickets')->firstOrFail();

        $onSaleTicketType = TicketType::query()->onSale()->active()->firstOrFail();
        $onSaleReservedTicketWithExpiration = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $onSaleTicketType->id,
        ]);

        // Not on sale ticket type, with set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $onSaleReservedTicketWithExpiration->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function cart_create_reserved_call_with_zero_quantity_type_returns_success(): void
    {
        $user = User::doesntHave('roles')->doesntHave('reservedTickets')->firstOrFail();

        $zeroQuantityTicketType = TicketType::query()->onSale()->active()->where('quantity', 0)->firstOrFail();
        $zeroQuantityTicketWithExpiration = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $zeroQuantityTicketType->id,
        ]);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $zeroQuantityTicketWithExpiration->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function cart_create_reserved_call_with_invalid_data_returns_error(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->firstOrFail();
        $availableReservedTicket = $user->availableReservedTickets->firstOrFail();

        $noReservedTicketTypes = TicketType::query()->doesntHave('reservedTickets')->available()->get();

        $expiredReservedTicket = ReservedTicket::create([
            'user_id' => $user->id,
            'expiration_date' => now()->subMinute(),
            'ticket_type_id' => $noReservedTicketTypes->firstOrFail()->id,
        ]);

        $inactiveTicketType = TicketType::where('active', false)->onSale()->firstOrFail();
        $inactiveReservedTicket = ReservedTicket::create([
            'user_id' => $user->id,
            'expiration_date' => now()->addMinute(),
            'ticket_type_id' => $inactiveTicketType->id,
        ]);

        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->doesntHave('reservedTickets')->active()->firstOrFail();
        $notOnSaleReservedTicketWithoutExpiration = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $notOnSaleTicketType->id,
        ]);

        $ticketType = TicketType::query()->available()->firstOrFail();
        $secondEventTicketType = TicketType::query()->available()->where('event_id', '!=', $ticketType->event_id)->firstOrFail();
        $firstTicket = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $ticketType->id,
        ]);
        $secondEventTicket = ReservedTicket::create([
            'user_id' => $user->id,
            'ticket_type_id' => $secondEventTicketType->id,
        ]);

        // Malformed request
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'random_data' => [1, 2, 3],
        ]);

        $response->assertStatus(422);
        // Invalid ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                'number',
            ],
        ]);

        $response->assertStatus(422);

        // Invalid Ticket ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                9999999,
            ],
        ]);

        $response->assertStatus(422);

        // Expired reserved ticket for ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $expiredReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Duplicate ticket types
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $availableReservedTicket->id,
                $availableReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Inactive ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $inactiveReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Not on sale ticket type, without set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $notOnSaleReservedTicketWithoutExpiration->id,
            ],
        ]);

        $response->assertStatus(422);

        // Two tickets from different events
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'reserved' => [
                $firstTicket->id,
                $secondEventTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // General and reserved have different events
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $firstTicket->ticketType->id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => [
                $secondEventTicket->id,
            ],
        ]);

        $response->assertStatus(422);
    }
}
