<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Events;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class EventShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.show';

    public array $routeParams = ['event' => 1];

    #[Test]
    public function event_show_call_while_not_logged_in_returns_active_event(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    #[Test]
    public function event_show_call_while_not_logged_in_does_not_return_pending_event(): void
    {
        $event = Event::where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function event_show_call_while_not_logged_in_does_not_return_trashed_event(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'with_trashed' => true]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function event_show_call_as_admin_returns_pending_event(): void
    {
        $event = Event::where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    #[Test]
    public function event_show_call_as_admin_returns_trashed_event(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    #[Test]
    public function events_view_call_with_ticket_types_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('ticketTypes')->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'ticketTypes']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.ticket_types'));
    }

    #[Test]
    public function events_view_call_with_purchased_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('purchasedTickets')->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_tickets'));
    }

    #[Test]
    public function events_view_call_with_reserved_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('reservedTickets')->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_tickets'));
    }

    #[Test]
    public function events_view_call_with_shift_types_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $event = Event::has('shiftTypes')->firstOrFail();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'shiftTypes']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.shift_types'));
    }
}
