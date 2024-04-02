<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use Tests\ApiRouteTestCase;

class MeOrdersTest extends ApiRouteTestCase
{
    public string $routeName = 'api.me.orders';

    public function test_me_orders_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_orders_call_as_user_returns_success(): void
    {
        $user = User::has('orders')->first();
        $orderCount = $user->orders->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($orderCount, $response->baseResponse->original->count());
    }
}
