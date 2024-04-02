<?php

declare(strict_types=1);

namespace Tests\Feature\Users\Tickets;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketsIndexTest extends ApiRouteTestCase
{
    public string $routeName = 'api.users.tickets.index';

    public array $routeParams = ['user' => 1];

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('purchasedTickets')->has('reservedTickets')->first();
        $this->routeParams = ['user' => $this->user->id];
        $this->buildEndpoint();
    }

    public function test_user_tickets_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_user_tickets_index_call_without_permission_returns_error(): void
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

    public function test_user_tickets_index_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->assertTrue($user->can('users.view'));
        $this->assertNotEquals($this->routeParams['user'], $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_user_tickets_index_call_to_own_id_without_permission_is_successful(): void
    {
        $this->assertFalse($this->user->can('users.view'));

        $response = $this->actingAs($this->user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($this->user->purchasedTickets, $response->baseResponse->original['data']['purchasedTickets']);
    }
}
