<?php

declare(strict_types=1);

namespace Tests\Feature\Grants\ArtProjects;

use App\Enums\RolesEnum;
use App\Models\Grants\ArtProject;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ArtProjectUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.update';

    public array $routeParams = ['art_project' => 1];

    protected ArtProject $artProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artProject = ArtProject::inRandomOrder()->firstOrFail();

        $this->buildEndpoint(params: ['art_project' => $this->artProject->id]);
    }

    #[Test]
    public function art_project_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->putJson($this->endpoint, [
            'name' => 'Updated Art Project',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function art_project_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad name
        $response = $this->actingAs($user)->putJson($this->endpoint, [
            'name' => ['array'],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function art_project_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('art-projects.update'));

        $response = $this->actingAs($user)->putJson($this->endpoint, [
            'name' => 'Updated Art Project',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function art_project_update_call_not_logged_in_returns_error(): void
    {
        $artProject = ArtProject::factory()->create();

        $response = $this->putJson($this->endpoint, [
            'name' => 'Updated Art Project',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(401);
    }
}
