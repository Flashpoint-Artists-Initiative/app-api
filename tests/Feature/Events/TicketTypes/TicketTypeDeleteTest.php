<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTypeDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.destroy';

    public array $routeParams = ['event' => 1, 'ticket_type' => 1];

    public function test_ticket_type_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_type_force_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_type_delete_call_without_permission_fails(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_type_delete_call_as_event_manager_succeeds(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_type_force_delete_call_as_event_manager_fails(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_type_force_delete_call_of_trashed_event_as_event_manager_fails(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $ticketType->delete();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_type_delete_call_as_admin_succeeds(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_type_force_delete_call_as_admin_succeeds(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_type_force_delete_call_of_trashed_event_as_admin_succeeds(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $ticketType->delete();

        $this->buildEndpoint(params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_type_delete_restore_call_as_admin_succeeds(): void
    {
        $event = Event::has('ticketTypes')->firstOrFail();
        $ticketType = $event->ticketTypes->firstOrFail();
        $ticketType->delete();

        $this->buildEndpoint(name: 'api.events.ticket-types.restore', params: ['event' => $event->id, 'ticket_type' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
    }
}
