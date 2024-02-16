<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Orders;

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\OrderSeeder;
use Database\Seeders\Testing\UserSeeder;
use Tests\ApiRouteTestCase;

class OrdersShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = OrderSeeder::class;

    public string $routeName = 'api.admin.orders.show';

    public array $routeParams = ['order' => 1];

    public function test_orders_view_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_orders_view_call_without_permission_returns_error(): void
    {
        $this->seed(UserSeeder::class);

        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('orders.view'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_orders_view_call_with_permission_is_successful(): void
    {
        $this->seed(UserSeeder::class);

        $user = User::role(RolesEnum::Admin)->first();

        $this->assertTrue($user->can('orders.view'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
