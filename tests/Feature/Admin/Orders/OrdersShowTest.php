<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Orders;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class OrdersShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.orders.show';

    public array $routeParams = ['order' => 1];

    #[Test]
    public function orders_view_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function orders_view_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('orders.view'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function orders_view_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('orders.view'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
