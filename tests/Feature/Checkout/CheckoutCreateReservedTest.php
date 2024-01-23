<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class CheckoutCreateReservedTest extends ApiRouteTestCase
{
    public string $routeName = 'api.checkout.store-reserved';

    public bool $seed = true;

    public function test_cart_create_reserved_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'tickets' => [
                1,
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_cart_create_reserved_call_with_valid_data_returns_success(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $user->availableReservedTickets->first()->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_reserved_call_with_valid_data_twice_returns_new_cart(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $user->availableReservedTickets->first()->id,
            ],
        ]);

        $response->assertStatus(201);

        $secondResponse = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $user->availableReservedTickets->first()->id,
            ],
        ]);

        $secondResponse->assertStatus(201);

        $this->assertNotEquals($response->decodeResponseJson()->json('data.clientSecret'), $secondResponse->decodeResponseJson()->json('data.clientSecret'));

        $this->assertCount(2, Cart::all());
        $this->assertCount(2, CartItem::all());
    }

    public function test_cart_create_reserved_call_with_expiration_date_returns_success(): void
    {
        $user = User::doesntHave('roles')->doesntHave('reservedTickets')->first();

        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->active()->first();
        $notOnSaleReservedTicketWithExpiration = $this->createReservedTicket([
            'user_id' => $user->id,
            'expiration_date' => now()->addMinute(),
            'ticket_type_id' => $notOnSaleTicketType->id,
        ]);

        // Not on sale ticket type, with set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $notOnSaleReservedTicketWithExpiration->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_reserved_call_with_invalid_data_returns_error(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->first();
        $availableReservedTicket = $user->availableReservedTickets->first();

        $noReservedTicketTypes = TicketType::query()->doesntHave('reservedTickets')->available()->get();
        $notEnoughReservedTicket = $this->createReservedTicket([
            'user_id' => $user->id,
            'expiration_date' => now()->addMinute(),
            'ticket_type_id' => $noReservedTicketTypes->first()->id,
        ]);

        $expiredReservedTicket = $this->createReservedTicket([
            'user_id' => $user->id,
            'expiration_date' => now()->subMinute(),
            'ticket_type_id' => $noReservedTicketTypes[1]->id,
        ]);

        $inactiveTicketType = TicketType::where('active', false)->onSale()->first();
        $inactiveReservedTicket = $this->createReservedTicket([
            'user_id' => $user->id,
            'expiration_date' => now()->addMinute(),
            'ticket_type_id' => $inactiveTicketType->id,
        ]);

        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->doesntHave('reservedTickets')->active()->first();
        $notOnSaleReservedTicketWithoutExpiration = $this->createReservedTicket([
            'user_id' => $user->id,
            'ticket_type_id' => $notOnSaleTicketType->id,
        ]);

        $ticketType = TicketType::query()->available()->first();

        // Malformed request
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'random_data' => [1, 2, 3],
        ]);

        $response->assertStatus(422);
        // Invalid ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                'number',
            ],
        ]);

        $response->assertStatus(422);

        // Invalid Ticket ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                9999999,
            ],
        ]);

        $response->assertStatus(422);

        // Expired reserved ticket for ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $expiredReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Duplicate ticket types
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $availableReservedTicket->id,
                $availableReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Inactive ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $inactiveReservedTicket->id,
            ],
        ]);

        $response->assertStatus(422);

        // Not on sale ticket type, without set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                $notOnSaleReservedTicketWithoutExpiration->id,
            ],
        ]);

        $response->assertStatus(422);
    }

    protected function createReservedTicket(array $args): ReservedTicket
    {
        $ticket = new ReservedTicket();
        foreach ($args as $k => $v) {
            $ticket->{$k} = $v;
        }

        $ticket->save();

        return $ticket;
    }
}
