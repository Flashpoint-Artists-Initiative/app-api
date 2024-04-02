<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Teams;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TeamDeleteTest extends ApiRouteTestCase
{
    public string $routeName = 'api.events.teams.destroy';

    public array $routeParams = ['event' => 1, 'team' => 1];

    public function test_team_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_team_force_delete_call_while_not_logged_in_fails(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_team_delete_call_without_permission_fails(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_team_force_delete_call_as_event_manager_fails(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::EventManager)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_team_force_delete_call_of_trashed_event_as_event_manager_fails(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $ticketType->delete();

        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::EventManager)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_team_delete_call_as_admin_succeeds(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_team_force_delete_call_as_admin_succeeds(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();

        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_team_force_delete_call_of_trashed_event_as_admin_succeeds(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $ticketType->delete();

        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_team_delete_restore_call_as_admin_succeeds(): void
    {
        $event = Event::has('teams')->first();
        $ticketType = $event->teams->first();
        $ticketType->delete();

        $this->buildEndpoint(name: 'api.events.teams.restore', params: ['event' => $event->id, 'team' => $ticketType->id, 'force' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
    }
}
