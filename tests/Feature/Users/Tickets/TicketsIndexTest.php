<?php

declare(strict_types=1);

namespace Tests\Feature\Users\Tickets;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketsIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.tickets.index';

    public array $routeParams = ['user' => 1];

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('purchasedTickets')->has('reservedTickets')->firstOrFail();
        $this->routeParams = ['user' => $this->user->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function user_tickets_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function user_tickets_index_call_without_permission_returns_error(): void
    {
        /** @var User $user */
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
    public function user_tickets_index_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('users.view'));
        $this->assertNotEquals($this->routeParams['user'], $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function user_tickets_index_call_to_own_id_without_permission_is_successful(): void
    {
        $this->assertFalse($this->user->can('users.view'));

        $response = $this->actingAs($this->user)->get($this->endpoint);

        $response->assertStatus(200);
        /** @var JsonResponse $baseResponse */
        $baseResponse = $response->baseResponse;
        $this->assertEquals($this->user->purchasedTickets, $baseResponse->original['data']['purchasedTickets']);
    }
}
