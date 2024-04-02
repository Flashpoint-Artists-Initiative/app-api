<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class UsersDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.destroy';

    public array $routeParams = ['user' => 1];

    public function test_users_delete_call_while_not_logged_in_fails(): void
    {
        $this->generateUserForDeletion();

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_users_force_delete_call_while_not_logged_in_fails(): void
    {
        $this->generateUserForDeletion(true);

        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_users_delete_call_without_permission_fails(): void
    {
        $this->generateUserForDeletion();

        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('users.delete'));

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_users_delete_call_as_admin_succeeds(): void
    {
        $this->generateUserForDeletion();

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_users_force_delete_call_as_admin_succeeds(): void
    {
        $this->generateUserForDeletion(true);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_users_force_delete_call_of_trashed_users_as_admin_succeeds(): void
    {
        $model = $this->generateUserForDeletion(true);
        $model->delete();

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_users_delete_restore_call_as_admin_succeeds(): void
    {
        $model = $this->generateUserForDeletion(true);
        $model->delete();
        $this->buildEndpoint(name: 'api.users.restore', params: ['user' => $model->id]);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_users_delete_call_as_self_succeeds(): void
    {
        $user = $this->generateUserForDeletion();

        $this->assertFalse($user->can('users.delete'));

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    protected function generateUserForDeletion(bool $force = false): User
    {
        $user = User::factory()->create([
            'email' => 'deleteme@example.com',
        ]);

        $this->buildEndpoint(params: ['user' => $user->id, 'force' => $force]);

        return $user;
    }
}
