<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use Tests\ApiRouteTestCase;

class MeTicketsTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.tickets';

    public function test_me_tickets_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_tickets_call_as_user_returns_success(): void
    {
        // Purchased Tickets
        $user = User::has('purchasedTickets')->firstOrFail();
        $purchasedTicketsCount = $user->purchasedTickets->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($purchasedTicketsCount, 'data.purchasedTickets');

        // Reserved Tickets
        $user = User::has('reservedTickets')->firstOrFail();
        $reservedTicketsCount = $user->reservedTickets->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($reservedTicketsCount, 'data.reservedTickets');
    }
}
