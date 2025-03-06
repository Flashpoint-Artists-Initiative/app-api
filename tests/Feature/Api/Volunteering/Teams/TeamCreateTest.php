<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\Teams;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TeamCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.teams.store';

    public array $routeParams = ['event' => 1];

    #[Test]
    public function team_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function team_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => ['abc', 'def'],
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(422);

        // Bad description
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => null,
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => 'invalid email',
            'active' => true,
        ]);

        $response->assertStatus(422);

        // Bad active
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function team_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('teams.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function team_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(401);
    }
}
