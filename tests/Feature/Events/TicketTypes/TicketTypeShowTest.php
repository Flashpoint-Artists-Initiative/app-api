<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\Testing\EventWithMultipleTicketTypesSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class TicketTypeShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventWithMultipleTicketTypesSeeder::class;

    public string $routeName = 'api.events.ticket-types.show';

    public array $routeParams = ['event' => 1, 'ticket_type' => 1];

    public function test_ticket_type_show_call_while_not_logged_in_returns_active_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    public function test_ticket_type_show_call_while_not_logged_in_does_not_return_pending_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->first();
        $ticket_type = $event->ticketTypes()->where('active', false)->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_type_show_call_while_not_logged_in_does_not_return_trashed_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->onlyTrashed()->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'with_trashed' => true]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(404);
    }

    public function test_ticket_type_show_call_as_admin_returns_pending_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->first();
        $ticket_type = $event->ticketTypes()->where('active', false)->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    public function test_ticket_type_show_call_as_admin_returns_trashed_ticket_type(): void
    {
        $event = Event::has('ticketTypes')->where('active', true)->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->onlyTrashed()->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticket_type->id);
    }

    public function test_ticket_types_view_call_with_purchased_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('purchasedTickets')->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_tickets'));
    }

    public function test_ticket_types_view_call_with_reserved_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('reservedTickets')->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_tickets'));
    }

    public function test_ticket_types_view_call_with_event_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('reservedTickets')->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'event']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.event'));
    }

    public function test_ticket_types_view_call_with_cart_items_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('reservedTickets')->first();
        $ticket_type = $event->ticketTypes()->where('active', true)->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticket_type->id, 'include' => 'cartItems']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.cart_items'));
    }
}
