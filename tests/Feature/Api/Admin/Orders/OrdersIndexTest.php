<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\Orders;

use App\Enums\RolesEnum;
use App\Models\Ticketing\Order;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class OrdersIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.orders.index';

    #[Test]
    public function orders_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function orders_index_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('orders.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function orders_index_call_with_permission_returns_success(): void
    {
        $count = Order::count();
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('orders.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $count);
    }
}
