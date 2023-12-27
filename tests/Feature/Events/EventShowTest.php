<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Tests\ApiRouteTestCase;

class EventShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

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

        $user = User::role(RolesEnum::SuperAdmin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }

    public function test_event_show_call_as_admin_returns_trashed_event(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::SuperAdmin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $event->id);
    }
}
