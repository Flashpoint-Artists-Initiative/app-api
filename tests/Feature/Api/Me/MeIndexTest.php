<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class MeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.index';

    #[Test]
    public function me_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_index_call_as_user_returns_success(): void
    {
        $user = User::firstOrFail();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $user->id);
    }

    #[Test]
    public function me_index_call_with_includes_returns_success(): void
    {
        // reservedTickets
        $user = User::has('reservedTickets')->firstOrFail();
        $this->buildEndpoint(params: ['include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($user->reservedTickets->count(), 'data.reserved_tickets');

        // purchasedTickets
        $user = User::has('purchasedTickets')->firstOrFail();
        $this->buildEndpoint(params: ['include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($user->purchasedTickets->count(), 'data.purchased_tickets');

        // orders
        $user = User::has('orders')->firstOrFail();
        $this->buildEndpoint(params: ['include' => 'orders']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($user->orders->count(), 'data.orders');

        // waivers
        $user = User::has('waivers')->firstOrFail();
        $this->buildEndpoint(params: ['include' => 'waivers']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($user->waivers->count(), 'data.waivers');
    }
}
