<?php

declare(strict_types=1);

namespace Tests\Feature\Api\TicketTypes\ReservedTickets;

use App\Enums\RolesEnum;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ReservedTicketDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.reserved-tickets.destroy';

    public array $routeParams = ['ticket_type' => 1, 'reserved_ticket' => 1];

    protected TicketType $ticketType;

    protected ReservedTicket $reservedTicket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('reservedTickets')->active()->firstOrFail();
        $this->reservedTicket = ReservedTicket::factory()->for($this->ticketType)->create();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);
    }

    #[Test]
    public function ticket_type_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_type_force_delete_call_while_not_logged_in_fails(): void
    {
        $this->addEndpointParams(['force' => true]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_type_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_type_delete_call_as_box_office_succeeds(): void
    {
        $user = User::role(RolesEnum::BoxOffice)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function ticket_type_delete_call_as_box_office_with_purchased_ticket_fails(): void
    {
        $user = User::role(RolesEnum::BoxOffice)->firstOrFail();
        $this->ticketType = TicketType::has('reservedTickets.purchasedTicket')->active()->firstOrFail();
        $this->reservedTicket = $this->ticketType->reservedTickets()->has('purchasedTicket')->firstOrFail();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
