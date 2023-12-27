<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiRouteTestCase;

class UsersShowTest extends ApiRouteTestCase
{
    use RefreshDatabase;

    public string $routeName = 'api.users.show';

    public array $routeParams = [1];

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

    // TODO: Uncomment this out when this is fixed
    // https://github.com/Flashpoint-Artists-Initiative/app-api/issues/4
    /*
    public function test_user_call_ok_with_own_non_admin(): void
    {
        $user = User::factory()->create([
            'legal_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'user',
        ]);

        // Override the endpoint to the route with the created user's id
        $endpoint = route($this->routeName, [$user->id], false);

        $response = $this->actingAs($user)->get($endpoint);

        $response->assertStatus(200);
    }
    */
}
