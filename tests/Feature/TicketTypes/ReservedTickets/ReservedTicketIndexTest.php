<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\ReservedTickets;

use App\Models\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class ReservedTicketIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.reserved-tickets.index';

    public array $routeParams = ['ticket_type' => 1];

    protected TicketType $ticketType;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('reservedTickets')->active()->first();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id]);
    }

    public function test_reserved_ticket_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_reserved_ticket_index_call_with_permission_returns_success(): void
    {
        $reservedTicketCount = $this->ticketType->reservedTickets()->count();
        $this->assertGreaterThan(0, $reservedTicketCount);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.view');
        $user->givePermissionTo('reservedTickets.viewAny');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200)->assertJsonPath('meta.total', $reservedTicketCount);
    }

    public function test_reserved_ticket_index_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }
}
