<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Tests\ApiRouteTestCase;

class TicketTypeUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.update';

    public array $routeParams = ['event' => 1, 'ticket_type' => 1];

    public function setUp(): void
    {
        parent::setUp();
        $event = Event::has('ticketTypes')->inRandomOrder()->first();
        $this->routeParams = [
            'event' => $event->id,
            'ticket_type' => $event->ticketTypes()->inRandomOrder()->first()->id,
        ];
        $this->buildEndpoint();
    }

    public function test_ticket_type_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'General Sale Ticket',
            'sale_start_date' => new Carbon('-1 day'),
            'sale_end_date' => new Carbon('+1 week'),
            'quantity' => 100,
            'price' => 50,
            'description' => fake()->paragraph(),
            'active' => true,
        ]);

        $response->assertStatus(200);
    }

    public function test_ticket_type_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        // Bad name
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => null,
        ]);

        $response->assertStatus(422);

        //Bad sale_start_date
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'sale_start_date' => 'bad_date',
        ]);

        $response->assertStatus(422);

        //Bad end_date
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'sale_end_date' => 'bad_date',
        ]);

        $response->assertStatus(422);

        //Bad description
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'description' => null,
        ]);

        $response->assertStatus(422);
    }

    public function test_ticket_type_update_call_without_permission_returns_error(): void
    {

        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('ticketTypes.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Ticket Type',
        ]);

        $response->assertStatus(403);
    }

    public function test_ticket_type_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'Test Ticket Type',
        ]);

        $response->assertStatus(401);
    }
}
