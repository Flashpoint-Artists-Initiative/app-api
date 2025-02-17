<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\Requirements;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Requirement;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class RequirementShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-requirements.show';

    public array $routeParams = ['shift_requirement' => 1];

    #[Test]
    public function requirement_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function requirement_show_call_without_permission_returns_requirement(): void
    {
        $requirement = Requirement::firstOrFail();
        $this->addEndpointParams(['shift_requirement' => $requirement->id]);

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function requirement_show_call_without_permission_does_not_return_trashed_requirement(): void
    {
        $requirement = Requirement::onlyTrashed()->firstOrFail();
        $this->addEndpointParams(['shift_requirement' => $requirement->id]);

        $user = User::doesntHave('roles')->firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }

    #[Test]
    public function requirement_show_call_as_admin_returns_trashed_requirement(): void
    {
        $requirement = Requirement::onlyTrashed()->firstOrFail();
        $this->addEndpointParams(['shift_requirement' => $requirement->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $requirement->id);
    }
}
