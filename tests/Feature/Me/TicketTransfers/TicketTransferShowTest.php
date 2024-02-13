<?php

declare(strict_types=1);

namespace Tests\Feature\Me\TicketTransfers;

use App\Models\User;
use Database\Seeders\Testing\TicketTransferSeeder;
use Tests\ApiRouteTestCase;

class TicketTransferShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = TicketTransferSeeder::class;

    public string $routeName = 'api.me.ticket-transfers.show';

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

    public function test_me_ticket_transfer_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_ticket_transfer_show_call_while_logged_in_returns_success(): void
    {
        $ticketTransfer = $this->user->ticketTransfers->first();

        $response = $this->actingAs($this->user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $ticketTransfer->id);
    }
}
