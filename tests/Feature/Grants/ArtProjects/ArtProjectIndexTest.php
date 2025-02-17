<?php

declare(strict_types=1);

namespace Tests\Feature\Grants\ArtProjects;

use App\Enums\ArtProjectStatus;
use App\Enums\RolesEnum;
use App\Models\Grants\ArtProject;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ArtProjectIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.index';

    #[Test]
    public function art_project_index_call_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function art_project_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->getJson($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function art_project_index_hides_non_approved_projects_for_users_without_permission(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
        $response->assertJsonMissing(['project_status' => ArtProjectStatus::PendingReview->value]);
        $response->assertJsonMissing(['project_status' => ArtProjectStatus::Denied->value]);
        $response->assertJsonFragment(['project_status' => ArtProjectStatus::Approved->value]);
    }

    #[Test]
    public function art_project_index_hides_soft_deleted_projects_for_users_without_permission(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();
        ArtProject::factory()->create(['project_status' => ArtProjectStatus::Approved, 'deleted_at' => now()]);

        $this->buildEndpoint(params: ['with_trashed' => true]);

        $projectCount = ArtProject::where('project_status', ArtProjectStatus::Approved)->withTrashed()->count();
        $existingProjectCount = ArtProject::where('project_status', ArtProjectStatus::Approved)->count();

        $this->assertGreaterThan($existingProjectCount, $projectCount);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $existingProjectCount);
    }

    #[Test]
    public function art_project_index_shows_soft_deleted_projects_for_users_with_permission(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        ArtProject::factory()->create(['deleted_at' => now()]);
        ArtProject::factory()->create();

        $this->buildEndpoint(params: ['with_trashed' => true]);

        $projectCount = ArtProject::withTrashed()->count();
        $existingProjectCount = ArtProject::count();

        $this->assertGreaterThan($existingProjectCount, $projectCount);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $projectCount);

    }
}
