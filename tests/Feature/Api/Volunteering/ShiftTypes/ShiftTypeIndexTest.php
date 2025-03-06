<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Team;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ShiftTypeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.teams.shift-types.index';

    public array $routeParams = ['team' => 1];

    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->team = Team::has('shiftTypes')->inRandomOrder()->firstOrFail();
        $this->routeParams = ['team' => $this->team->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function shift_type_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function shift_type_index_call_for_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftTypeCount = $team->shiftTypes()->count();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($shiftTypeCount, 'data');
    }

    #[Test]
    public function shift_type_index_call_for_active_event_and_inactive_team_returns_error(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_index_call_for_inactive_event_and_active_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_index_call_for_inactive_event_and_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }

    #[Test]
    public function shift_type_index_call_for_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }
}
