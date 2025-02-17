<?php

declare(strict_types=1);

namespace Tests\Feature\Grants\ArtProjects;

use App\Enums\ArtProjectStatus;
use App\Enums\RolesEnum;
use App\Models\Grants\ArtProject;
use App\Models\User;
use Tests\ApiRouteTestCase;

class ArtProjectShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.show';

    public array $routeParams = ['art_project' => 1];

    protected ArtProject $artProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artProject = ArtProject::where('project_status', ArtProjectStatus::Approved)->firstOrFail();

        $this->buildEndpoint(params: ['art_project' => $this->artProject->id]);
    }

    public function test_art_project_show_call_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $artProject = ArtProject::factory()->create();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_art_project_show_call_not_logged_in_returns_error(): void
    {
        $artProject = ArtProject::factory()->create();

        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_art_project_show_call_as_admin_returns_trashed_art_project(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create(['deleted_at' => now()]);
        $this->buildEndpoint(params: ['art_project' => $artProject->id, 'with_trashed' => true]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_art_project_show_call_as_admin_does_not_return_trashed_art_project(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create(['deleted_at' => now()]);
        $this->buildEndpoint(params: ['art_project' => $artProject->id, 'with_trashed' => true]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(404);
    }

    public function test_art_project_show_call_as_artist_returns_pending_art_project(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::PendingReview,
            'user_id' => $user->id,
        ]);
        $this->buildEndpoint(params: ['art_project' => $artProject->id]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_art_project_show_call_as_admin_returns_pending_art_project(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::PendingReview,
        ]);
        $this->buildEndpoint(params: ['art_project' => $artProject->id]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_art_project_show_call_as_non_artist_does_not_return_pending_art_project(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::PendingReview,
        ]);
        $this->buildEndpoint(params: ['art_project' => $artProject->id]);

        $response = $this->actingAs($user)->getJson($this->endpoint);

        $response->assertStatus(403);
    }
}
