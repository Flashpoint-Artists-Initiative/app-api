<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Shifts;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use Tests\ApiRouteTestCase;

class ShiftUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.update';

    public array $routeParams = ['shift_type' => 1, 'shift' => 1];

    public Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();
        $shiftType = ShiftType::has('shifts')->firstOrFail();
        $this->shift = $shiftType->shifts()->firstOrFail();
        $this->routeParams = ['shift_type' => $shiftType->id, 'shift' => $this->shift->id];
        $this->buildEndpoint();
    }

    public function test_shift_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(200);
    }

    public function test_shift_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad start_offset
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'start_offset' => null,
        ]);

        $response->assertStatus(422);

        // Bad multiplier
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'multiplier' => -1,
        ]);

        $response->assertStatus(422);

        // Bad length
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'length' => 'string',
        ]);

        $response->assertStatus(422);

        // Bad num_spots
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'num_spots' => 'string',
        ]);

        $response->assertStatus(422);
    }

    public function test_shift_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'start_offset' => 1000,
        ]);

        $response->assertStatus(403);
    }

    public function test_shift_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'start_offset' => 1000,
        ]);

        $response->assertStatus(401);
    }
}
