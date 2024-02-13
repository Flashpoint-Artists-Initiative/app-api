<?php

declare(strict_types=1);

namespace Tests\Feature\Me\TicketTransfers;

use App\Models\User;
use Database\Seeders\Testing\TicketTransferSeeder;
use Tests\ApiRouteTestCase;

class TicketTransferIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = TicketTransferSeeder::class;

    public string $routeName = 'api.me.ticket-transfers.index';

    public array $routeParams = [];

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('ticketTransfers')->first();
    }

    public function test_me_ticket_transfer_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_ticket_transfer_index_call_while_logged_in_returns_success(): void
    {
        $response = $this->actingAs($this->user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
