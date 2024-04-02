<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Shifts;

use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\Team;
use Tests\ApiRouteTestCase;

class ShiftSignupCancelTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shifts.signups.cancel';

    public array $routeParams = ['shift' => 1];

    public function test_shift_cancel_call_returns_a_successful_response(): void
    {
        $user = User::factory()->create();
        $preSignup = Shift::find(1);
        $this->assertCount(0, $preSignup->volunteers);

        $preSignup->volunteers()->attach($user);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(204);

        $postSignup = Shift::find(1);
        $this->assertCount(0, $postSignup->volunteers);
    }

    public function test_shift_cancel_call_for_invalid_shift_returns_a_successful_response(): void
    {
        $user = User::factory()->create();
        $preSignup = Shift::find(1);
        $this->assertCount(0, $preSignup->volunteers);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(422);
    }

    public function test_shift_cancel_call_for_inactive_team_returns_error(): void
    {
        $team = Team::where('active', false)->has('shifts')->first();
        $shift = $team->shifts->first();

        $this->addEndpointParams(['shift' => $shift->id]);

        $user = User::factory()->create();
        $shift->volunteers()->attach($user);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_cancel_call_for_inactive_event_returns_error(): void
    {
        $event = Event::where('active', false)->has('teams.shifts')->first();
        $team = $event->teams()->where('active', true)->has('shifts')->first();
        $shift = $team->shifts->first();

        $this->addEndpointParams(['shift' => $shift->id]);

        $user = User::factory()->create();
        $shift->volunteers()->attach($user);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_cancel_call_not_logged_in_returns_error(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }
}
