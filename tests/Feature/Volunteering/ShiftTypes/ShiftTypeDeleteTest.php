<?php

declare(strict_types=1);

namespace Tests\Feature\Volunteering\ShiftTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\Team;
use Database\Seeders\Testing\ShiftTypeSeeder;
use Tests\ApiRouteTestCase;

class ShiftTypeDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = ShiftTypeSeeder::class;

    public string $routeName = 'api.teams.shift-types.destroy';

    public array $routeParams = ['team' => 1, 'shift_type' => 1];

    public function setUp(): void
    {
        parent::setUp();
        $team = Team::has('shiftTypes')->inRandomOrder()->first();
        $this->routeParams = ['team' => $team->id, 'shift_type' => $team->shiftTypes->first()->id];
        $this->buildEndpoint();
    }

    public function test_shift_type_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_shift_type_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_shift_type_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }
}
