<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me\TicketTransfers;

use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferCompleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.ticket-transfers.complete';

    public array $routeParams = ['ticket_transfer' => 1];

    public User $user;

    public TicketTransfer $transfer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transfer = TicketTransfer::firstOrFail();
        $this->user = User::factory()->create(['email' => $this->transfer->recipient_email]);
        $this->routeParams = [
            'ticket_transfer' => $this->transfer->id,
        ];
        $this->buildEndpoint();
    }

    #[Test]
    public function me_ticket_transfer_complete_call_while_not_logged_in_fails(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_ticket_transfer_complete_call_while_logged_in_succeeds(): void
    {
        $response = $this->actingAs($this->user)->post($this->endpoint);
        $ticket = $this->transfer->purchasedTickets->firstOrFail();

        $this->assertNotEquals($this->user->id, $ticket->user_id);

        $response->assertStatus(204);

        $ticket->refresh();

        $this->assertEquals($this->user->id, $ticket->user_id);
    }

    #[Test]
    public function me_ticket_transfer_complete_call_for_completed_transfer_fails(): void
    {
        $ticket = $this->transfer->purchasedTickets->firstOrFail();

        $this->transfer->completed = true;
        $this->transfer->saveQuietly();

        $this->assertNotEquals($this->user->id, $ticket->user_id);

        $response = $this->actingAs($this->user)->post($this->endpoint);

        $response->assertStatus(403);

        $this->assertNotEquals($this->user->id, $ticket->user_id);
    }

    #[Test]
    public function me_ticket_transfer_complete_call_for_invalid_transfer_fails(): void
    {
        $transfer = TicketTransfer::where('recipient_email', '!=', $this->user->email)->firstOrFail();
        $ticket = $transfer->purchasedTickets->firstOrFail();
        $this->buildEndpoint(params: ['ticket_transfer' => $transfer->id]);

        $this->assertNotEquals($this->user->email, $transfer->recipient_email);

        $response = $this->actingAs($this->user)->post($this->endpoint);

        $response->assertStatus(403);

    }
}
