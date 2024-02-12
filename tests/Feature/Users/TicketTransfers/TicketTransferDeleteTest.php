<?php

declare(strict_types=1);

namespace Tests\Feature\Users\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\Testing\TicketTransferSeeder;
use Tests\ApiRouteTestCase;

class TicketTransferDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = TicketTransferSeeder::class;

    public string $routeName = 'api.users.ticket-transfers.destroy';

    public array $routeParams = ['user' => 1];

    public User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::has('ticketTransfers')->first();
        $this->routeParams = [
            'user' => $this->user->id,
            'ticket_transfer' => $this->user->ticketTransfers->first()->id,
        ];
        $this->buildEndpoint();
    }

    public function test_ticket_transfer_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_transfer_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->id == $this->user->id);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_transfer_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_transfer_delete_call_for_completed_transfer_fails(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $transfer = $this->user->ticketTransfers->first();
        $transfer->completed = true;
        $transfer->saveQuietly();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
