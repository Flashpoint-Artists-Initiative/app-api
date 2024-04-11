<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\PurchasedTickets;

use App\Models\Ticketing\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class PurchasedTicketIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.purchased-tickets.index';

    public array $routeParams = ['ticket_type' => 1];

    public function test_purchased_ticket_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_purchased_ticket_index_call_without_permission_returns_only_that_users_tickets(): void
    {
        $ticketType = TicketType::has('purchasedTickets')->active()->firstOrFail();
        $user = $ticketType->purchasedTickets()->firstOrFail()->user()->firstOrFail();
        $userPurchasedTicketsCount = $user->purchasedTickets()->count();

        $this->assertFalse($user->can('purchasedTickets.viewAny'));
        $this->assertGreaterThan(0, $userPurchasedTicketsCount);

        $this->buildEndpoint(params: ['ticket_type' => $ticketType->id]);

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($userPurchasedTicketsCount, $response->baseResponse->original->count());
    }

    public function test_purchased_ticket_index_call_with_permission_returns_success(): void
    {
        $ticketType = TicketType::has('purchasedTickets')->active()->firstOrFail();
        $purchasedTicketsCount = $ticketType->purchasedTickets()->count();

        $this->buildEndpoint(params: ['ticket_type' => $ticketType->id]);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('purchasedTickets.viewAny');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);

        $this->assertEquals($purchasedTicketsCount, $response->baseResponse->original->count());
    }
}
