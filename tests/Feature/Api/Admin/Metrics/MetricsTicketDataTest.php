<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Admin\Metrics;

use App\Enums\RolesEnum;
use App\Models\Ticketing\Order;
use App\Models\User;
use App\Services\StripeService;
use Database\Seeders\AccurateOrderSeeder;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Stripe\TaxRate;
use Tests\ApiRouteTestCase;

class MetricsTicketDataTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.metrics.ticketData';

    public array $routeParams = ['event_id' => 1];

    #[Test]
    public function metrics_ticket_data_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function metrics_ticket_data_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('metrics.ticketData'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function metrics_ticket_data_call_with_permission_returns_success(): void
    {
        $this->seed(AccurateOrderSeeder::class);
        $order = Order::oldest()->firstOrFail();  // The oldest order (by created_at) will have the correct event_id

        $this->addEndpointParams(['event_id' => $order->event_id]);

        $count = $order->event->ticketTypes()->sum('quantity');
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('metrics.ticketData'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonStructure(['data' => ['total', 'individual']]);
    }
}
