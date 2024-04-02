<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Shifts;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use Tests\ApiRouteTestCase;

class ShiftIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.index';

    public array $routeParams = ['shift_type' => 1];

    public ShiftType $shiftType;

    public function setUp(): void
    {
        parent::setUp();
        $this->shiftType = ShiftType::has('shifts')->first();
        $this->routeParams = ['shift_type' => $this->shiftType->id];
        $this->buildEndpoint();
    }

    public function test_shift_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_shift_index_call_for_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->first();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $shiftCount = Shift::where('shift_type_id', $shiftType->id)->count();

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($shiftCount, $response->baseResponse->original->count());
    }

    public function test_shift_index_call_for_active_event_and_inactive_team_returns_failure(): void
    {
        $event = Event::where('active', true)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    public function test_shift_index_call_for_inactive_event_and_active_team_returns_failure(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    public function test_shift_index_call_for_inactive_event_and_team_returns_failure(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    public function test_shift_index_call_for_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->first();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes()->first();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }
}
