<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\ShiftType;
use App\Models\Volunteering\Team;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ShiftTypeShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.teams.shift-types.show';

    public array $routeParams = ['team' => 1, 'shift_type' => 1];

    protected ShiftType $shiftType;

    protected function setUp(): void
    {
        parent::setUp();
        $team = Team::has('shiftTypes')->firstOrFail();
        $this->shiftType = $team->shiftTypes()->firstOrFail();
        $this->buildEndpoint(params: ['team' => $team->id, 'shift_type' => $this->shiftType->id]);
    }

    #[Test]
    public function shift_type_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function shift_type_show_call_with_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $shiftType->id);
    }

    #[Test]
    public function shift_type_show_call_with_active_event_and_inactive_team_returns_error(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_show_call_with_inactive_event_and_active_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_show_call_with_inactive_event_and_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_show_call_with_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $this->addEndpointParams(['team' => $team->id, 'shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
