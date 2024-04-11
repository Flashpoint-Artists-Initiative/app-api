<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Team;
use Tests\ApiRouteTestCase;

class ShiftTypeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.teams.shift-types.index';

    public array $routeParams = ['team' => 1];

    protected Team $team;

    public function setUp(): void
    {
        parent::setUp();
        $this->team = Team::has('shiftTypes')->inRandomOrder()->firstOrFail();
        $this->routeParams = ['team' => $this->team->id];
        $this->buildEndpoint();
    }

    public function test_shift_type_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_shift_type_index_call_for_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftTypeCount = $team->shiftTypes()->count();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($shiftTypeCount, $response->baseResponse->original->count());
    }

    public function test_shift_type_index_call_for_active_event_and_inactive_team_returns_error(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    public function test_shift_type_index_call_for_inactive_event_and_active_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    public function test_shift_type_index_call_for_inactive_event_and_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }

    public function test_shift_type_index_call_for_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $this->addEndpointParams(['team' => $team->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }
}
