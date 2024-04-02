<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use Tests\ApiRouteTestCase;

class MeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.index';

    public function test_me_index_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_index_call_as_user_returns_success(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $user->id);
    }

    public function test_me_index_call_with_includes_returns_success(): void
    {
        // reservedTickets
        $user = User::has('reservedTickets')->first();
        $this->buildEndpoint(params: ['include' => 'reservedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($user->reservedTickets->count(), $response->baseResponse->original->reservedTickets->count());

        // purchasedTickets
        $user = User::has('purchasedTickets')->first();
        $this->buildEndpoint(params: ['include' => 'purchasedTickets']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($user->purchasedTickets->count(), $response->baseResponse->original->purchasedTickets->count());

        // orders
        $user = User::has('orders')->first();
        $this->buildEndpoint(params: ['include' => 'orders']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($user->orders->count(), $response->baseResponse->original->orders->count());

        // waivers
        $user = User::has('waivers')->first();
        $this->buildEndpoint(params: ['include' => 'waivers']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($user->waivers->count(), $response->baseResponse->original->waivers->count());
    }
}
