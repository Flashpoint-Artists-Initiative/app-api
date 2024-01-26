<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\Testing\UserSeeder;
use Tests\ApiRouteTestCase;

class UsersIndexTest extends ApiRouteTestCase
{
    public string $routeName = 'api.users.index';

    public function test_users_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_users_index_call_without_permission_returns_error(): void
    {
        $this->seed(UserSeeder::class);

        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('users.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_users_index_call_with_permission_returns_success(): void
    {
        $this->seed(UserSeeder::class);

        $user_count = User::count();
        $user = User::role(RolesEnum::SuperAdmin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $user_count);
    }
}
