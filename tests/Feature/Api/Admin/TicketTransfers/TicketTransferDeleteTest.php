<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.ticket-transfers.destroy';

    public array $routeParams = ['ticket_transfer' => 1];

    #[Test]
    public function ticket_transfer_admin_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_transfer_admin_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_transfer_admin_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function ticket_transfer_admin_delete_call_for_completed_transfer_fails(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $transfer = TicketTransfer::where('completed', false)->firstOrFail();
        $transfer->completed = true;
        $transfer->saveQuietly();

        $this->addEndpointParams(['ticket_transfer' => $transfer->id]);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
