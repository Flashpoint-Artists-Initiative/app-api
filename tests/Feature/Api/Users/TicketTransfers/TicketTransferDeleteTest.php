<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Users\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.ticket-transfers.destroy';

    public array $routeParams = ['user' => 1];

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

    #[Test]
    public function ticket_transfer_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_transfer_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->id == $this->user->id);

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_transfer_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function ticket_transfer_delete_call_for_completed_transfer_fails(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $transfer = $this->user->ticketTransfers->firstOrFail();
        $transfer->completed = true;
        $transfer->saveQuietly();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }
}
