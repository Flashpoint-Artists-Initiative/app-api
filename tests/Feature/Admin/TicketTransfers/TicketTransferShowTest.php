<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.ticket-transfers.show';

    public array $routeParams = ['ticket_transfer' => 1];

    #[Test]
    public function ticket_transfer_admin_show_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_transfer_admin_show_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_transfer_admin_show_call_with_permission_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $this->routeParams['ticket_transfer']);
    }
}
