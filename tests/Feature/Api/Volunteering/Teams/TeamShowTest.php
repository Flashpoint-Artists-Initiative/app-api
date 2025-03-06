<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\Teams;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TeamShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.teams.show';

    public array $routeParams = ['event' => 1, 'team' => 1];

    #[Test]
    public function team_show_call_while_not_logged_in_returns_error(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id]);

        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function team_show_call_without_permission_returns_active_team(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', true)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $team->id);
    }

    #[Test]
    public function team_show_call_without_permission_does_not_return_pending_team(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function team_show_call_without_permission_does_not_return_trashed_team(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id, 'with_trashed' => true]);

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function team_show_call_as_admin_returns_pending_team(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', false)->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $team->id);
    }

    #[Test]
    public function team_show_call_as_admin_returns_trashed_team(): void
    {
        $event = Event::has('teams')->where('active', true)->firstOrFail();
        $team = $event->teams()->where('active', true)->onlyTrashed()->firstOrFail();
        $this->buildEndpoint(params: ['event' => $event->id, 'team' => $team->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $team->id);
    }

    #[Test]
    public function team_show_for_inactive_event_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->firstOrFail();
        $this->addEndpointParams(['event' => $event->id, 'team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function team_show_for_inactive_event_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->firstOrFail();
        $this->addEndpointParams(['event' => $event->id, 'team' => $team->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }
}
