<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\Testing\ShiftRequirementSeeder;
use Tests\ApiRouteTestCase;

class RequirementUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftRequirementSeeder::class;

    public string $routeName = 'api.shift-requirements.update';

    public array $routeParams = ['shift_requirement' => 1];

    public function test_requirement_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => fake()->word(),
            'icon' => fake()->word(),
            'description' => fake()->paragraph(),
        ]);

        $response->assertStatus(200);
    }

    public function test_requirement_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        // Bad name
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => null,
        ]);

        $response->assertStatus(422);

        //Bad icon
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'icon' => null,
        ]);

        $response->assertStatus(422);

        //Bad description
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'description' => null,
        ]);

        $response->assertStatus(422);
    }

    public function test_requirement_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'update',
        ]);

        $response->assertStatus(403);
    }

    public function test_requirement_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'update',
        ]);

        $response->assertStatus(401);
    }
}
