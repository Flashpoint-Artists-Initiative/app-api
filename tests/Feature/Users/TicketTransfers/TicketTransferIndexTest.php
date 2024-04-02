<?php

declare(strict_types=1);

namespace Tests\Feature\Users\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTransferIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.ticket-transfers.index';

    public array $routeParams = ['user' => 1];

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('ticketTransfers')->first();
        $this->routeParams['user'] = $this->user->id;
        $this->buildEndpoint();
    }

    public function test_ticket_transfer_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_transfer_index_call_without_permission_returns_success(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_transfer_index_call_with_permission_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
