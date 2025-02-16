<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use Carbon\Carbon;
use Tests\ApiRouteTestCase;

class TicketTypeCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.store';

    public array $routeParams = ['event' => 1];

    public function test_ticket_type_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 100,
            'price' => 50,
            'description' => fake()->paragraph(),
            'active' => true,
        ]);

        $response->assertStatus(201);
    }

    public function test_ticket_type_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => null,
            'description' => 'Description',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(422);

        // Bad sale_start_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'description' => 'Description',
            'sale_start_date' => 'bad date',
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(422);

        // Bad sale_end_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'description' => 'Description',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => 'bad date',
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(422);

        // Bad description
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'description' => [],
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(422);

        // Bad quantity
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'description' => [],
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => -5,
            'price' => 50,
        ]);

        $response->assertStatus(422);

        // Bad price
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'description' => [],
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 'fifty dollars',
        ]);

        $response->assertStatus(422);
    }

    public function test_ticket_type_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('ticketTypes.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'name' => 'General Sale',
            'description' => 'Description',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(403);
    }

    public function test_ticket_type_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'name' => 'General Sale',
            'description' => 'Description',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 10,
            'price' => 50,
        ]);

        $response->assertStatus(401);
    }
}
