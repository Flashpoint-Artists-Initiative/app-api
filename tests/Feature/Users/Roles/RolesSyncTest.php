<?php

declare(strict_types=1);

namespace Tests\Feature\Users\Roles;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class RolesSyncTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.roles.sync';

    public array $routeParams = ['user' => 1];

    #[Test]
    public function users_roles_sync_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $this->buildEndpoint(params: ['user' => $user->id]);

        $this->assertCount(1, $user->roles);
        $this->assertEquals($user->roles->modelKeys(), [1]);

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'resources' => [2],
        ]);

        $response->assertStatus(200);

        $user->load('roles');

        $this->assertCount(1, $user->roles);
        $this->assertEquals($user->roles->modelKeys(), [2]);
    }

    #[Test]
    public function users_roles_sync_call_without_permissons_returns_an_error(): void
    {
        /** @var User $user */
        $user = User::with('roles')->findOrFail(1);
        $this->assertFalse($user->can('roles.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(403);
    }
}
