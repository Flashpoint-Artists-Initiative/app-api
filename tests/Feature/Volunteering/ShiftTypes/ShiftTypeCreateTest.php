<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class ShiftTypeCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.teams.shift-types.store';

    public array $routeParams = ['team' => 1];

    public function test_shift_type_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(201);
    }

    public function test_shift_type_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad title
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => null,
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(422);

        // Bad description
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => null,
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(422);

        // Bad length
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => fake()->paragraph(),
            'length' => 'length',
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(422);

        // Bad num_spots
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    public function test_shift_type_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('shiftTypes.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(403);
    }

    public function test_shift_type_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'title' => 'Test Shift Type',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(401);
    }
}
