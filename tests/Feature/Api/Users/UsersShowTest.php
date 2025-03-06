<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Users;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class UsersShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.show';

    public array $routeParams = ['user' => 1];

    #[Test]
    public function users_view_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function users_view_call_without_permission_returns_error(): void
    {
        $user = User::factory()->create([
            'email' => 'newuser@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($user->can('users.view'));
        $this->assertNotEquals($this->routeParams['user'], $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function users_view_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('users.view'));
        $this->assertNotEquals($this->routeParams['user'], $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function users_view_call_to_own_id_without_permission_is_successful(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('users.view'));

        // Override the endpoint to the route with the specified user's id
        $this->buildEndpoint(params: ['user' => $user->id]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $user->id);
    }

    #[Test]
    public function users_view_call_with_roles_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->buildEndpoint(params: ['user' => $user->id, 'include' => 'roles']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.roles'));
    }

    #[Test]
    public function users_view_call_with_permissions_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->buildEndpoint(params: ['user' => $user->id, 'include' => 'permissions']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.permissions'));
    }

    #[Test]
    public function users_view_call_with_purchased_tickets_is_successful(): void
    {
        $user = User::has('purchasedTickets')->firstOrFail();

        $this->buildEndpoint(params: ['user' => $user->id, 'include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.purchased_tickets'));
    }

    #[Test]
    public function users_view_call_with_reserved_tickets_is_successful(): void
    {
        $user = User::has('reservedTickets')->firstOrFail();

        $this->buildEndpoint(params: ['user' => $user->id, 'include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_tickets'));
    }
}
