<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\RolesEnum;
use App\Models\User;
use OwenIt\Auditing\Models\Audit;
use Tests\ApiRouteTestCase;

class UsersHistoryTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.history';

    public array $routeParams = ['user' => 1];

    public function test_users_history_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_users_history_call_without_permission_returns_error(): void
    {
        $user = User::factory()->create([
            'email' => 'newuser@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($user->can('users.history'));
        $this->assertNotEquals($this->routeParams['user'], $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_users_history_call_with_permission_is_successful(): void
    {
        $admin = User::role(RolesEnum::Admin)->first();

        // Create an audit
        Audit::create([
            'user_id' => $this->routeParams['user'],
            'user_type' => 'user',
            'event' => 'created',
            'auditable_type' => 'event',
            'auditable_id' => 1,
            'old_values' => [],
            'new_values' => [],
        ]);

        $this->assertTrue($admin->can('users.history'));
        $this->assertNotEquals($this->routeParams['user'], $admin->id);

        $response = $this->actingAs($admin)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.0.user_id', $this->routeParams['user']);
    }

    public function test_users_history_call_to_own_id_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('users.history'));

        // Override the endpoint to the route with the specified user's id
        $this->buildEndpoint(params: ['user' => $user->id]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }
}
