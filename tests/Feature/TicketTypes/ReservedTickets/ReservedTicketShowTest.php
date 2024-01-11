<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\ReservedTickets;

use App\Enums\RolesEnum;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class ReservedTicketShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.reserved-tickets.show';

    public array $routeParams = ['ticket_type' => 1, 'reserved_ticket' => 1];

    protected TicketType $ticketType;

    protected ReservedTicket $reservedTicket;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('purchasedTickets')->active()->first();
        $this->reservedTicket = $this->ticketType->reservedTickets()->has('purchasedTicket')->first();
        $this->routeParams = ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id];
        $this->buildEndpoint();
    }

    public function test_reserved_ticket_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_reserved_ticket_show_call_with_permission_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $this->reservedTicket->id);
    }

    public function test_reserved_tickets_view_call_with_purchased_ticket_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->addEndpointParams(['include' => 'purchasedTicket']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_ticket'));
    }

    public function test_reserved_tickets_view_call_with_event_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->addEndpointParams(['include' => 'event']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.event'));
    }

    public function test_reserved_tickets_view_call_with_user_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->addEndpointParams(['include' => 'user']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.user'));
    }
}
