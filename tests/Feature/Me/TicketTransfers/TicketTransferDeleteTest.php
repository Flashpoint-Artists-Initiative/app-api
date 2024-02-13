<?php

declare(strict_types=1);

namespace Tests\Feature\Me\TicketTransfers;

use App\Models\User;
use Database\Seeders\Testing\TicketTransferSeeder;
use Tests\ApiRouteTestCase;

class TicketTransferDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = TicketTransferSeeder::class;

    public string $routeName = 'api.me.ticket-transfers.destroy';

    public array $routeParams = ['ticket_transfer' => 1];

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('ticketTransfers')->first();
        $this->routeParams = [
            'ticket_transfer' => $this->user->ticketTransfers->first()->id,
        ];
        $this->buildEndpoint();
    }

    public function test_me_ticket_transfer_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_ticket_transfer_delete_call_while_logged_in_succeeds(): void
    {
        $response = $this->actingAs($this->user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_me_ticket_transfer_delete_call_for_completed_transfer_fails(): void
    {
        $transfer = $this->user->ticketTransfers->first();
        $transfer->completed = true;
        $transfer->saveQuietly();

        $response = $this->actingAs($this->user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
