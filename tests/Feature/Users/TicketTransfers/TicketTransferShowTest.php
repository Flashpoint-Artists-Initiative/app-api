<?php

declare(strict_types=1);

namespace Tests\Feature\Users\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTransferShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.ticket-transfers.show';

    public array $routeParams = ['user' => 1, 'ticket_transfer' => 1];

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::has('ticketTransfers')->firstOrFail();
        $this->routeParams = [
            'user' => $this->user->id,
            'ticket_transfer' => $this->user->ticketTransfers->firstOrFail()->id,
        ];

        $this->buildEndpoint();
    }

    public function test_ticket_transfer_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_transfer_show_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_transfer_show_call_with_permission_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $ticketTransfer = $this->user->ticketTransfers->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticketTransfer->id);
    }
}
