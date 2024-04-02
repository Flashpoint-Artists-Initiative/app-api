<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Orders;

use App\Enums\RolesEnum;
use App\Models\Ticketing\Order;
use App\Models\User;
use Tests\ApiRouteTestCase;

class OrdersIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.orders.index';

    public function test_orders_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_orders_index_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('orders.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_orders_index_call_with_permission_returns_success(): void
    {
        $count = Order::count();
        $user = User::role(RolesEnum::Admin)->first();

        $this->assertTrue($user->can('orders.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $count);
    }
}
