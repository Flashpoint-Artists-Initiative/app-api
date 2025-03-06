<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Events;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class EventDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.destroy';

    public array $routeParams = ['event' => 1];

    #[Test]
    public function event_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function event_force_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'force' => true]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function event_delete_call_without_permission_fails(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function event_delete_call_as_event_manager_succeeds(): void
    {
        $event = Event::where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function event_force_delete_call_as_event_manager_fails(): void
    {
        $event = Event::where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'force' => true]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function event_force_delete_call_of_trashed_event_as_event_manager_fails(): void
    {
        $event = Event::where('active', false)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::role(RolesEnum::EventManager)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function event_delete_call_as_admin_succeeds(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function event_force_delete_call_as_admin_succeeds(): void
    {
        $event = Event::where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function event_force_delete_call_of_trashed_event_as_admin_succeeds(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function event_delete_restore_call_as_admin_succeeds(): void
    {
        $event = Event::where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(name: 'api.events.restore', params: ['event' => $event->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
    }
}
