<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Grants\ArtProjects;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ArtProjectCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.store';

    #[Test]
    public function art_project_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Art Project',
            'user_id' => $user->id,
            'event_id' => 1,
            'description' => 'Test description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function art_project_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => ['array'],
            'user_id' => $user->id,
            'event_id' => 1,
            'description' => 'Test description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $response->assertStatus(422);

        // Missing name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'user_id' => $user->id,
            'event_id' => 1,
            'description' => 'Test description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function art_project_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('art-projects.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'Test Art Project',
            'user_id' => $user->id,
            'event_id' => 1,
            'description' => 'Test description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function art_project_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'name' => 'Test Art Project',
            'user_id' => 1,
            'event_id' => 1,
            'description' => 'Test description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $response->assertStatus(401);
    }
}
