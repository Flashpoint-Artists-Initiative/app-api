<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Grants\ArtProjects;

use App\Enums\ArtProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ArtProjectVoteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.art-projects.vote';

    public array $routeParams = ['art_project' => 1];

    protected ArtProject $artProject;

    protected function setUp(): void
    {
        parent::setUp();
        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatusEnum::Approved->value,
            'event_id' => $event->id]
        );
        $this->artProject = $artProject;

        $this->buildEndpoint(params: ['art_project' => $this->artProject->id]);
    }

    #[Test]
    public function art_project_vote_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
        $this->assertTrue($this->artProject->votes->contains($user));
    }

    #[Test]
    public function art_project_vote_call_without_permission_returns_a_successful_response(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('artProjects.vote'));

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function art_project_vote_call_a_second_time_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('artProjects.vote'));

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);

        $secondResponse = $this->actingAs($user)->post($this->endpoint);
        $secondResponse->assertStatus(400);
    }

    #[Test]
    public function art_project_vote_call_not_logged_in_returns_error(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }
}
