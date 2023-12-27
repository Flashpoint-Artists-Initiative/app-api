<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiRouteTestCase;

class UsersIndexTest extends ApiRouteTestCase
{
    use RefreshDatabase;

    public string $routeName = 'api.users.index';

    public function test_user_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_user_call_without_admin_role_returns_error(): void
    {
        $user = User::factory()->create([
            'legal_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'user',
        ]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_user_call_ok_with_admin_role(): void
    {
        $user = User::factory()->create([
            'legal_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'user',
        ])->assignRole('Admin');

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
