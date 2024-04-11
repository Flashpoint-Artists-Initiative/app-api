<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Shifts;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class ShiftCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.store';

    public array $routeParams = ['shift_type' => 1];

    public function test_shift_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // With optional fields
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(201);

        // Without optional fields
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
        ]);

        $response->assertStatus(201);
    }

    public function test_shift_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad start_offset
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => 'string',
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(422);

        //Bad multiplier
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => 'string',
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(422);

        //Bad length
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => -1,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(422);

        //Bad num_spots
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => -1,
        ]);

        $response->assertStatus(422);
    }

    public function test_shift_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('shifts.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(403);
    }

    public function test_shift_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'start_offset' => fake()->numberBetween(0, 10000),
            'multiplier' => fake()->numberBetween(1, 3),
            'length' => fake()->numberBetween(1, 7) * 30,
            'num_spots' => fake()->numberBetween(1, 6),
        ]);

        $response->assertStatus(401);
    }
}
