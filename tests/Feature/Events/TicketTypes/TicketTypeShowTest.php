<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTypeShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.show';

    public array $routeParams = ['event' => 1, 'ticket_type' => 1];

    #[Test]
    public function ticket_type_show_call_while_not_logged_in_returns_active_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->firstOrFail();
        $ticket_type = $event->ticketTypes()->where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    #[Test]
    public function ticket_type_show_call_while_not_logged_in_does_not_return_pending_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->firstOrFail();
        $ticket_type = TicketType::factory()->for($event)->inactive()->create();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_type_show_call_while_not_logged_in_does_not_return_trashed_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->firstOrFail();
        $ticket_type = TicketType::factory()->for($event)->trashed()->create();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'with_trashed' => true]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function ticket_type_show_call_as_admin_returns_pending_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->firstOrFail();
        $ticket_type = TicketType::factory()->for($event)->inactive()->create();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    #[Test]
    public function ticket_type_show_call_as_admin_returns_trashed_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->firstOrFail();
        $ticket_type = TicketType::factory()->for($event)->trashed()->create();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    #[Test]
    public function ticket_types_view_call_with_purchased_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('purchasedTickets')->firstOrFail();
        $ticket_type = $event->ticketTypes()->where('active', true)->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_tickets'));
    }

    #[Test]
    public function ticket_types_view_call_with_reserved_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('reservedTickets')->firstOrFail();
        $ticket_type = $event->ticketTypes()->where('active', true)->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_tickets'));
    }

    #[Test]
    public function ticket_types_view_call_with_event_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('reservedTickets')->firstOrFail();
        $ticket_type = $event->ticketTypes()->where('active', true)->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'event']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.event'));
    }

    #[Test]
    public function ticket_types_view_call_with_cart_items_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('reservedTickets')->firstOrFail();
        $ticket_type = $event->ticketTypes()->where('active', true)->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'cartItems']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.cart_items'));
    }
}
