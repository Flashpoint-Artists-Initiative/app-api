<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\Testing\EventSeeder;
use Database\Seeders\Testing\ShiftTypeSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class EventShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventSeeder::class;

    public string $routeName = 'api.events.show';

    public array $routeParams = ['event' => 1];

    public function test_event_show_call_while_not_logged_in_returns_active_event(): void
    {
        $event = Event::where('active', true)->first();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    public function test_event_show_call_while_not_logged_in_does_not_return_pending_event(): void
    {
        $event = Event::where('active', false)->first();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_event_show_call_while_not_logged_in_does_not_return_trashed_event(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'with_trashed' => true]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(404);
    }

    public function test_event_show_call_as_admin_returns_pending_event(): void
    {
        $event = Event::where('active', false)->first();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    public function test_event_show_call_as_admin_returns_trashed_event(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    public function test_events_view_call_with_ticket_types_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('ticketTypes')->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'ticketTypes']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.ticket_types'));
    }

    public function test_events_view_call_with_purchased_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('purchasedTickets')->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_tickets'));
    }

    public function test_events_view_call_with_reserved_tickets_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('reservedTickets')->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_tickets'));
    }

    public function test_events_view_call_with_shift_types_is_successful(): void
    {
        $this->seed(ShiftTypeSeeder::class);

        $user = User::role(RolesEnum::Admin)->first();
        $event = Event::has('shiftTypes')->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'include' => 'shiftTypes']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.shift_types'));
    }
}
