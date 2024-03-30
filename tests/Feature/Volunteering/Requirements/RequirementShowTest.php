<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Requirement;
use Database\Seeders\Testing\ShiftRequirementSeeder;
use Tests\ApiRouteTestCase;

class RequirementShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftRequirementSeeder::class;

    public string $routeName = 'api.shift-requirements.show';

    public array $routeParams = ['shift_requirement' => 1];

    public function test_requirement_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_requirement_show_call_without_permission_returns_requirement(): void
    {
        $requirement = Requirement::first();
        $this->addEndpointParams(['shift_requirement' => $requirement->id]);

        $user = User::doesntHave('roles')->first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_requirement_show_call_without_permission_does_not_return_trashed_requirement(): void
    {
        $requirement = Requirement::onlyTrashed()->first();
        $this->addEndpointParams(['shift_requirement' => $requirement->id]);

        $user = User::doesntHave('roles')->first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }

    public function test_requirement_show_call_as_admin_returns_trashed_requirement(): void
    {
        $requirement = Requirement::onlyTrashed()->first();
        $this->addEndpointParams(['shift_requirement' => $requirement->id, 'with_trashed' => true]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $requirement->id);
    }
}
