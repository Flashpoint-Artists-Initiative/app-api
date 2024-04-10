<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Shifts;

use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use Tests\ApiRouteTestCase;

class ShiftShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.show';

    public array $routeParams = ['shift_type' => 1, 'shift' => 1];

    protected Shift $shift;

    public function setUp(): void
    {
        parent::setUp();
        $shiftType = ShiftType::has('shifts')->firstOrFail();
        $this->shift = $shiftType->shifts()->firstOrFail();
        $this->routeParams = ['shift_type' => $shiftType->id, 'shift' => $this->shift->id];
        $this->buildEndpoint();
    }

    public function test_shift_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_shift_show_call_for_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes.shifts')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $shift = $shiftType->shifts()->firstOrFail();
        $this->addEndpointParams(['shift_type' => $shiftType->id, 'shift' => $shift->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $shift->id);
    }

    public function test_shift_show_call_for_active_event_and_inactive_team_returns_error(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes.shifts')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $shift = $shiftType->shifts()->firstOrFail();
        $this->addEndpointParams(['shift_type' => $shiftType->id, 'shift' => $shift->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_show_call_for_inactive_event_and_active_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes.shifts')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $shift = $shiftType->shifts()->firstOrFail();
        $this->addEndpointParams(['shift_type' => $shiftType->id, 'shift' => $shift->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_show_call_for_inactive_event_and_team_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes.shifts')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();
        $shift = $shiftType->shifts()->firstOrFail();
        $this->addEndpointParams(['shift_type' => $shiftType->id, 'shift' => $shift->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }
}
