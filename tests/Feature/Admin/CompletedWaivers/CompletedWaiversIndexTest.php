<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\CompletedWaivers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\User;
use Database\Seeders\Testing\WaiverSeeder;
use Tests\ApiRouteTestCase;

class CompletedWaiversIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = WaiverSeeder::class;

    public string $routeName = 'api.admin.completed-waivers.index';

    public function test_completed_waivers_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_completed_waivers_index_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('users.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_completed_waivers_index_call_with_permission_returns_success(): void
    {
        $count = CompletedWaiver::count();
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $count);
    }
}
