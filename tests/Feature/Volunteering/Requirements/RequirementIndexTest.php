<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Models\User;
use App\Models\Volunteering\Requirement;
use Database\Seeders\Testing\ShiftRequirementSeeder;
use Tests\ApiRouteTestCase;

class RequirementIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftRequirementSeeder::class;

    public string $routeName = 'api.shift-requirements.index';

    public function test_requirement_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_requirement_index_call_without_permission_ignores_trashed_types(): void
    {
        $requirementCount = Requirement::count();
        $totalRequirementCount = Requirement::withTrashed()->count();

        $this->assertLessThan($totalRequirementCount, $requirementCount);

        $this->addEndpointParams(['with_trashed' => true]);

        $user = User::doesntHave('roles')->first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($requirementCount, $response->baseResponse->original->count());
    }

    public function test_requirement_index_call_with_permission_returns_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $requirementCount = Requirement::count();
        $totalRequirementCount = Requirement::withTrashed()->count();

        $this->assertLessThan($totalRequirementCount, $requirementCount);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('requirements.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($totalRequirementCount, $response->baseResponse->original->count());
    }
}
