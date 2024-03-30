<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\ShiftType;
use App\Models\Volunteering\Team;
use Database\Seeders\Testing\ShiftTypeSeeder;
use Tests\ApiRouteTestCase;

class ShiftTypeShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftTypeSeeder::class;

    public string $routeName = 'api.teams.shift-types.show';

    public array $routeParams = ['team' => 1, 'shift_type' => 1];

    protected ShiftType $shiftType;

    public function setUp(): void
    {
        parent::setUp();
        $team = Team::has('shiftTypes')->first();
        $this->shiftType = $team->shiftTypes()->first();
        $this->buildEndpoint(params: ['team' => $team->id, 'shift_type' => $this->shiftType->id]);
    }

    public function test_shift_type_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_shift_type_show_call_with_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->first();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $shiftType->id);
    }

    public function test_shift_type_show_call_with_active_event_and_inactive_team_returns_error(): void
    {
        $event = Event::where('active', true)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_type_show_call_with_inactive_event_and_active_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_type_show_call_with_inactive_event_and_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_type_show_call_with_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
