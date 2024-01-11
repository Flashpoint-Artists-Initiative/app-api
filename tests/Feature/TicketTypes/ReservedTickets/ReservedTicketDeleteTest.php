<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\ReservedTickets;

use App\Enums\RolesEnum;
use App\Models\ReservedTicket;
use App\Models\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class ReservedTicketDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.reserved-tickets.destroy';

    public array $routeParams = ['ticket_type' => 1, 'reserved_ticket' => 1];

    protected TicketType $ticketType;

    protected ReservedTicket $reservedTicket;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('reservedTickets')->active()->first();
        $this->reservedTicket = $this->ticketType->reservedTickets()->first();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);
    }

    public function test_ticket_type_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_type_force_delete_call_while_not_logged_in_fails(): void
    {
        $this->addEndpointParams(['force' => true]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_type_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_type_delete_call_as_box_office_succeeds(): void
    {
        $user = User::role(RolesEnum::BoxOffice)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }
}
