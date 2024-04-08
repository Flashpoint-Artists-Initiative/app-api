<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTransferIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.ticket-transfers.index';

    public function test_ticket_transfer_admin_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_ticket_transfer_admin_index_call_without_permission_returns_success(): void
    {
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_ticket_transfer_admin_index_call_with_permission_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
