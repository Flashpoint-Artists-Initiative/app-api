<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\Shifts;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ShiftIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.index';

    public array $routeParams = ['shift_type' => 1];

    public ShiftType $shiftType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shiftType = ShiftType::has('shifts')->firstOrFail();
        $this->routeParams = ['shift_type' => $this->shiftType->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function shift_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function shift_index_call_for_active_event_and_team_returns_success(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $shiftCount = Shift::where('shift_type_id', $shiftType->id)->count();

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $response->assertJsonCount($shiftCount, 'data');
    }

    #[Test]
    public function shift_index_call_for_active_event_and_inactive_team_returns_failure(): void
    {
        $event = Event::where('active', true)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function shift_index_call_for_inactive_event_and_active_team_returns_failure(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', true)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function shift_index_call_for_inactive_event_and_team_returns_failure(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(403);
    }

    #[Test]
    public function shift_index_call_for_inactive_event_and_team_as_admin_returns_success(): void
    {
        $event = Event::where('active', false)->has('teams')->firstOrFail();
        $team = $event->teams()->where('active', false)->has('shiftTypes')->firstOrFail();
        $shiftType = $team->shiftTypes()->firstOrFail();

        $this->addEndpointParams(['shift_type' => $shiftType->id]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
    }
}
