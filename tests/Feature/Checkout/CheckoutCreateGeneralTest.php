<?php

declare(strict_types=1);

namespace Tests\Feature\Checkout;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class CheckoutCreateGeneralTest extends ApiRouteTestCase
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
        ]);

        $response->assertStatus(401);
    }

    public function test_cart_create_call_with_valid_data_returns_success(): void
    {
        $user = User::doesntHave('roles')->first();
        $ticketType = TicketType::query()->available()->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_call_with_valid_data_twice_returns_new_cart(): void
    {
        $user = User::doesntHave('roles')->first();
        $ticketType = TicketType::query()->available()->first();
        $cartCount = Cart::count();
        $cartItemCount = CartItem::count();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(201);

        $secondResponse = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $secondResponse->assertStatus(201);

        $this->assertNotEquals($response->decodeResponseJson()->json('data.clientSecret'), $secondResponse->decodeResponseJson()->json('data.clientSecret'));

        $this->assertCount($cartCount + 2, Cart::all());
        $this->assertCount($cartItemCount + 2, CartItem::all());
    }

    public function test_cart_create_call_with_general_and_reserved_data_returns_success(): void
    {
        $user = User::doesntHave('roles')->first();
        $ticketType = TicketType::query()->available()->first();
        $sameTicketType = TicketType::query()->event($ticketType->event_id)->where('id', '!=', $ticketType->id)->available()->first();
        $reservedTicket = ReservedTicket::create([
            'ticket_type_id' => $sameTicketType->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 2,
                ],
            ],
            'reserved' => [
                $reservedTicket->id,
            ],
        ]);

        $response->assertStatus(201);
    }

    public function test_cart_create_call_with_invalid_data_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();
        $ticketType = TicketType::query()->available()->first();
        $sameTicketType = TicketType::query()->event($ticketType->event_id)->where('id', '!=', $ticketType->id)->available()->first();
        $differentTicketType = TicketType::where('event_id', '!=', $ticketType->event_id)->available()->first();
        $inactiveTicketType = TicketType::where('active', false)->onSale()->hasQuantity()->first();
        $noQuantityTicketType = TicketType::where('quantity', 0)->onSale()->active()->first();
        $notOnSaleTicketType = TicketType::where('sale_start_date', '>=', now())->active()->hasQuantity()->first();
        $soldOutTicketType = TicketType::has('purchasedTickets')->available()->first();

        // Malformed request
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'random_data' => [1, 2, 3],
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
        ]);

        $response->assertStatus(422);

        // Invalid Quantity
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 'one',
                ],
            ],
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
        ]);

        $response->assertStatus(422);

        // Quantity above max
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 100,
                ],
            ],
        ]);

        $response->assertStatus(422);

        // Total quantity above max
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 3,
                ],
                [
                    'id' => $sameTicketType->id,
                    'quantity' => 3,
                ],
            ],
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
        ]);

        $response->assertStatus(422);

        // Tickets from different events
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $ticketType->id,
                    'quantity' => 1,
                ],
                [
                    'id' => $differentTicketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        // Inactive ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $inactiveTicketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        // No quantity ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $noQuantityTicketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        // Not on sale ticket type
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $notOnSaleTicketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        // Sold out ticket type
        $soldOutTicketType->quantity = $soldOutTicketType->purchased_tickets_count - 1;
        $soldOutTicketType->save();
        $soldOutTicketType->refresh();
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'tickets' => [
                [
                    'id' => $soldOutTicketType->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }
}
