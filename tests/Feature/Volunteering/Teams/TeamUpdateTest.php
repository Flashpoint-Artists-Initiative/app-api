<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Teams;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TeamUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.teams.update';

    public array $routeParams = ['event' => 1, 'team' => 1];

    protected function setUp(): void
    {
        parent::setUp();
        $event = Event::has('teams')->inRandomOrder()->firstOrFail();
        $this->routeParams = [
            'event' => $event->id,
            'team' => $event->teams()->inRandomOrder()->firstOrFail()->id,
        ];
        $this->buildEndpoint();
    }

    #[Test]
    public function team_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Team Update',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function team_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad name
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => null,
        ]);

        $response->assertStatus(422);

        // Bad description
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'description' => null,
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'bad email',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function team_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('teams.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Team Update',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function team_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'Test Team Update',
        ]);

        $response->assertStatus(401);
    }
}
