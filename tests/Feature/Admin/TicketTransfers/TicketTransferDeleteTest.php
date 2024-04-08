<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTransferDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.ticket-transfers.destroy';

    public array $routeParams = ['ticket_transfer' => 1];

    public function test_ticket_transfer_admin_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_transfer_admin_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_transfer_admin_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_ticket_transfer_admin_delete_call_for_completed_transfer_fails(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $transfer = TicketTransfer::where('completed', false)->first();
        $transfer->completed = true;
        $transfer->saveQuietly();

        $this->addEndpointParams(['ticket_transfer' => $transfer->id]);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
