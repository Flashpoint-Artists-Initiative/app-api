<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Team;
use Database\Seeders\Testing\ShiftTypeSeeder;
use Tests\ApiRouteTestCase;

class ShiftTypeUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftTypeSeeder::class;

    public string $routeName = 'api.teams.shift-types.update';

    public array $routeParams = ['team' => 1, 'shift_type' => 1];

    public function setUp(): void
    {
        parent::setUp();
        $team = Team::has('shiftTypes')->inRandomOrder()->first();
        $this->routeParams = [
            'team' => $team->id,
            'shift_type' => $team->shiftTypes()->inRandomOrder()->first()->id,
        ];
        $this->buildEndpoint();
    }

    public function test_shift_type_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => 'Test Shift Type Update',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(200);
    }

    public function test_shift_type_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        // Bad title
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => null,
        ]);

        $response->assertStatus(422);

        //Bad description
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'description' => null,
        ]);

        $response->assertStatus(422);

        //Bad length
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'length' => 'bad length',
        ]);

        $response->assertStatus(422);

        //Bad num_spots
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'length' => 'bad num_spots',
        ]);

        $response->assertStatus(422);
    }

    public function test_shift_type_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('shiftTypes.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Shift Type Update',
        ]);

        $response->assertStatus(403);
    }

    public function test_shift_type_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'Test Shift Type Update',
        ]);

        $response->assertStatus(401);
    }
}
