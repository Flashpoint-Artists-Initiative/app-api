<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes\Requirements;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\ShiftType;
use App\Models\Volunteering\Team;
use Tests\ApiRouteTestCase;

class ShiftRequirementDetachTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.requirements.detach';

    public array $routeParams = ['shift_type' => 1];

    public function test_shift_requirement_detach_call_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $shiftType = ShiftType::find(1);
        $shiftType->requirements()->attach(1);
        $shiftType->refresh();
        $this->assertCount(1, $shiftType->requirements);

        $response = $this->actingAs($user)->deleteJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(200);

        $shiftType->refresh();
        $this->assertCount(0, $shiftType->requirements);
    }

    public function test_shift_requirement_detach_call_for_inactive_team_returns_success(): void
    {
        $team = Team::where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes->first();

        $shiftType->requirements()->attach(1);
        $shiftType->refresh();
        $this->assertCount(1, $shiftType->requirements);

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->deleteJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(200);

        $shiftType->refresh();
        $this->assertCount(0, $shiftType->requirements);
    }

    public function test_shift_requirement_detach_call_for_inactive_event_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams.shiftTypes')->first();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->first();
        $shiftType = $team->shiftTypes->first();

        $shiftType->requirements()->attach(1);
        $shiftType->refresh();
        $this->assertCount(1, $shiftType->requirements);

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->deleteJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(200);

        $shiftType->refresh();
        $this->assertCount(0, $shiftType->requirements);
    }

    public function test_shift_requirement_detach_call_not_logged_in_returns_error(): void
    {
        $response = $this->deleteJson($this->endpoint);

        $response->assertStatus(401);
    }
}
