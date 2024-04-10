<?php

declare(strict_types=1);

namespace Tests\Feature\Users\Roles;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class RolesAttachTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.roles.attach';

    public array $routeParams = ['user' => 1];

    public function test_users_roles_attach_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $preAttach = User::with('roles')->findOrFail(1);
        $this->assertCount(0, $preAttach->roles);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(200);

        $postAttach = User::with('roles')->findOrFail(1);

        $this->assertCount(1, $postAttach->roles);
    }

    public function test_users_roles_attach_call_without_permissons_returns_an_error(): void
    {
        /** @var User $user */
        $user = User::with('roles')->findOrFail(1);
        $this->assertFalse($user->can('roles.update'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(403);
    }
}
