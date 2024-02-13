<?php

declare(strict_types=1);

namespace Tests\Feature\Me\TicketTransfers;

use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use Database\Seeders\Testing\TicketTransferSeeder;
use Tests\ApiRouteTestCase;

class TicketTransferCompleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = TicketTransferSeeder::class;

    public string $routeName = 'api.me.ticket-transfers.complete';

    public array $routeParams = ['ticket_transfer' => 1];

    public User $user;

    public TicketTransfer $transfer;

    public function setUp(): void
    {
        parent::setUp();
        $this->transfer = TicketTransfer::first();
        $this->user = User::factory()->create(['email' => $this->transfer->recipient_email]);
        $this->routeParams = [
            'ticket_transfer' => $this->transfer->id,
        ];
        $this->buildEndpoint();
    }

    public function test_me_ticket_transfer_complete_call_while_not_logged_in_fails(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_ticket_transfer_complete_call_while_logged_in_succeeds(): void
    {
        $response = $this->actingAs($this->user)->post($this->endpoint);
        $ticket = $this->transfer->purchasedTickets->first();

        $this->assertNotEquals($this->user->id, $ticket->user_id);

        $response->assertStatus(204);

        $ticket->refresh();

        $this->assertEquals($this->user->id, $ticket->user_id);
    }

    public function test_me_ticket_transfer_complete_call_for_completed_transfer_fails(): void
    {
        $ticket = $this->transfer->purchasedTickets->first();

        $this->transfer->completed = true;
        $this->transfer->saveQuietly();

        $this->assertNotEquals($this->user->id, $ticket->user_id);

        $response = $this->actingAs($this->user)->post($this->endpoint);

        $response->assertStatus(403);

        $this->assertNotEquals($this->user->id, $ticket->user_id);
    }

    public function test_me_ticket_transfer_complete_call_for_invalid_transfer_fails(): void
    {
        $transfer = TicketTransfer::where('recipient_email', '!=', $this->user->email)->first();
        $ticket = $transfer->purchasedTickets->first();
        $this->buildEndpoint(params: ['ticket_transfer' => $transfer->id]);

        $this->assertNotEquals($this->user->email, $transfer->recipient_email);

        $response = $this->actingAs($this->user)->post($this->endpoint);

        $response->assertStatus(403);

    }
}
