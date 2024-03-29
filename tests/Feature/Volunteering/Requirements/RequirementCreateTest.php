<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Requirements;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\Testing\ShiftTypeSeeder;
use Tests\ApiRouteTestCase;

class RequirementCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftTypeSeeder::class;

    public string $routeName = 'api.shift-requirements.store';

    public function test_requirement_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => fake()->word(),
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(201);
    }

    public function test_requirement_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        // Bad name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => null,
            'icon' => fake()->word(),
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(422);

        //Bad icon
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => null,
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(422);

        //Bad description
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => fake()->word(),
            'description' => null,
        ]);

        $response->assertStatus(422);
    }

    public function test_requirement_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => fake()->word(),
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(403);
    }

    public function test_requirement_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => fake()->word(),
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(401);
    }
}
