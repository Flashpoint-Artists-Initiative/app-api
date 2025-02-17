<?php

declare(strict_types=1);

namespace Tests\Feature\Users\Roles;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class RolesDetachTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.roles.detach';

    public array $routeParams = ['user' => 1];

    #[Test]
    public function users_roles_detach_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $this->buildEndpoint(params: ['user' => $user->id]);

        $this->assertCount(1, $user->roles);

        $response = $this->actingAs($user)->deleteJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(200);

        $user->load('roles');

        $this->assertCount(0, $user->roles);
    }

    #[Test]
    public function users_roles_detach_call_without_permissons_returns_an_error(): void
    {
        /** @var User $user */
        $user = User::with('roles')->findOrFail(1);
        $this->assertFalse($user->can('roles.update'));

        $response = $this->actingAs($user)->deleteJson($this->endpoint, [
            'resources' => [1],
        ]);

        $response->assertStatus(403);
    }
}
