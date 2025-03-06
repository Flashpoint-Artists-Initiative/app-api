<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\Teams;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Team;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TeamIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.teams.index';

    public array $routeParams = ['event' => 1];

    public Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::has('teams')->where('active', true)->inRandomOrder()->firstOrFail();
        $this->routeParams = ['event' => $this->event->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function team_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function team_index_call_without_permission_returns_only_active_teams(): void
    {
        $teamCount = Team::active()->event($this->event->id)->count();

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $response->assertJsonCount($teamCount, 'data');
    }

    #[Test]
    public function team_index_call_with_permission_returns_pending_types(): void
    {
        $activeTeamCount = Team::active()->event($this->event->id)->count();
        $teamCount = Team::query()->event($this->event->id)->count();

        $this->assertGreaterThan($activeTeamCount, $teamCount);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('teams.viewPending');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($teamCount, 'data');
    }

    #[Test]
    public function team_index_call_with_permission_returns_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $existingTeamCount = Team::where('active', true)->event($this->event->id)->count();
        $teamCount = Team::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existingTeamCount, $teamCount);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('teams.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($teamCount, 'data');
    }

    #[Test]
    public function team_index_call_without_permission_ignores_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $existingTeamCount = Team::where('active', true)->event($this->event->id)->count();
        $teamCount = Team::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existingTeamCount, $teamCount);

        // No permission for teams.viewDeleted
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        // Matches existing event count, not trashed
        $response->assertJsonCount($existingTeamCount, 'data');
    }

    #[Test]
    public function team_index_call_with_only_trashed_returns_correct_types(): void
    {
        $this->addEndpointParams(['only_trashed' => true]);

        $trashedTeamCount = Team::where('active', true)->event($this->event->id)->onlyTrashed()->count();
        $teamCount = Team::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertLessThan($teamCount, $trashedTeamCount);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('teams.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($trashedTeamCount, 'data');
    }

    #[Test]
    public function team_index_call_as_admin_returns_all_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $team_count = Team::where('event_id', $this->event->id)->count();
        $all_team_count = Team::withTrashed()->event($this->event->id)->count();

        $this->assertGreaterThan($team_count, $all_team_count);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($all_team_count, 'data');
    }

    #[Test]
    public function team_index_for_inactive_event_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $this->addEndpointParams(['event' => $event->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function team_index_for_inactive_event_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $this->addEndpointParams(['event' => $event->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }
}
