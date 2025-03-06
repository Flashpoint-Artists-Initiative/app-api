<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Team;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ShiftTypeUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.teams.shift-types.update';

    public array $routeParams = ['team' => 1, 'shift_type' => 1];

    protected function setUp(): void
    {
        parent::setUp();
        $team = Team::has('shiftTypes')->inRandomOrder()->firstOrFail();
        $this->routeParams = [
            'team' => $team->id,
            'shift_type' => $team->shiftTypes()->inRandomOrder()->firstOrFail()->id,
        ];
        $this->buildEndpoint();
    }

    #[Test]
    public function shift_type_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => 'Test Shift Type Update',
            'description' => fake()->paragraph(),
            'length' => fake()->numberBetween(1, 8) * 30,
            'num_spots' => fake()->numberBetween(1, 7),
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function shift_type_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad title
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => null,
        ]);

        $response->assertStatus(422);

        // Bad description
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'description' => null,
        ]);

        $response->assertStatus(422);

        // Bad length
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'length' => 'bad length',
        ]);

        $response->assertStatus(422);

        // Bad num_spots
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'length' => 'bad num_spots',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function shift_type_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('shiftTypes.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Shift Type Update',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function shift_type_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'Test Shift Type Update',
        ]);

        $response->assertStatus(401);
    }
}
