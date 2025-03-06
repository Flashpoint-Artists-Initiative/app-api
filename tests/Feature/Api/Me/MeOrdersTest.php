<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class MeOrdersTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.orders';

    #[Test]
    public function me_orders_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_orders_call_as_user_returns_success(): void
    {
        $user = User::has('orders')->firstOrFail();
        $orderCount = $user->orders->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($orderCount, 'data');
    }
}
