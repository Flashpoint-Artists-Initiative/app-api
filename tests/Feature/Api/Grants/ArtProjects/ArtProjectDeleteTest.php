<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Grants\ArtProjects;

use App\Enums\RolesEnum;
use App\Models\Grants\ArtProject;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ArtProjectDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.destroy';

    public array $routeParams = ['art_project' => 1];

    protected ArtProject $artProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artProject = ArtProject::inRandomOrder()->firstOrFail();

        $this->buildEndpoint(params: ['art_project' => $this->artProject->id]);
    }

    #[Test]
    public function art_project_delete_call_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function art_project_delete_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();
        $artProject = ArtProject::factory()->create();

        $this->assertFalse($user->can('art-projects.delete'));

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function art_project_delete_call_not_logged_in_returns_error(): void
    {
        $artProject = ArtProject::factory()->create();

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }
}
