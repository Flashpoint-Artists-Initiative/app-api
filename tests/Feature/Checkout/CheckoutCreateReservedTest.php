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
    public string $routeName = 'api.checkout.store';

    public bool $seed = true;

    public function test_cart_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => 1,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(401);
    }

    public function test_cart_create_call_with_valid_data_returns_success(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $user->availableReservedTickets->first()->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_call_with_valid_data_twice_returns_new_cart(): void
    {
        $user = User::doesntHave('roles')->has('availableReservedTickets')->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $user->availableReservedTickets->first()->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(201);

        $secondResponse = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $user->availableReservedTickets->first()->ticket_type_id,
                    'quantity' => 2,
                ],
            ],
            'reserved' => true,
        ]);

        $secondResponse->assertStatus(201);

        $this->assertNotEquals($response->decodeResponseJson()->json('data.id'), $secondResponse->decodeResponseJson()->json('data.id'));

        $this->assertCount(1, Cart::all());
        $this->assertCount(1, CartItem::all());
    }

    public function test_cart_create_call_with_expiration_date_returns_success(): void
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
                [
                    'id' => $notOnSaleReservedTicketWithExpiration->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_call_with_invalid_data_returns_error(): void
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
            'reserved' => true,
        ]);

        $response->assertStatus(422);
        // Invalid ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => 'number',
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Invalid Quantity
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $availableReservedTicket->ticket_type_id,
                    'quantity' => 'one',
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Invalid Ticket ID
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => 9999999,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // No reserved ticket for ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $noReservedTicketTypes->last()->id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Expired reserved ticket for ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $expiredReservedTicket->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Duplicate ticket types
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Not enough reserved for quantity requested
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $notEnoughReservedTicket->ticket_type_id,
                    'quantity' => 3,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Inactive ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $inactiveReservedTicket->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
        ]);

        $response->assertStatus(422);

        // Not on sale ticket type, without set expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $notOnSaleReservedTicketWithoutExpiration->ticket_type_id,
                    'quantity' => 1,
                ],
            ],
            'reserved' => true,
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
