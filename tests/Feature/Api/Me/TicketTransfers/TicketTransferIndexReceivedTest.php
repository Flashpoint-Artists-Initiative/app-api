<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me\TicketTransfers;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferIndexReceivedTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.ticket-transfers.index.received';

    public array $routeParams = [];

    public User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('ticketTransfers')->firstOrFail();
    }

    #[Test]
    public function me_ticket_transfer_index_received_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_ticket_transfer_index_received_call_while_logged_in_returns_success(): void
    {
        $response = $this->actingAs($this->user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
