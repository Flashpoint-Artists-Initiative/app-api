<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\ReservedTickets;

use App\Enums\RolesEnum;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Testing\EventWithMultipleTicketTypesSeeder;
use Tests\ApiRouteTestCase;

class ReservedTicketUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventWithMultipleTicketTypesSeeder::class;

    public string $routeName = 'api.ticket-types.reserved-tickets.update';

    public array $routeParams = ['ticket_type' => 1, 'reserved_ticket' => 1];

    protected TicketType $ticketType;

    protected ReservedTicket $reservedTicket;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('reservedTickets.purchasedTicket')->active()->first();
        $this->reservedTicket = $this->ticketType->reservedTickets()->has('purchasedTicket')->first();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);
    }

    public function test_reserved_ticket_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->reservedTicket = $this->ticketType->reservedTickets()->doesntHave('purchasedTicket')->first();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(200);
    }

    public function test_reserved_ticket_update_call_for_zero_price_type_creates_a_purchased_ticket(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->ticketType = TicketType::where('price', 0)->first();
        $this->reservedTicket = ReservedTicket::factory()->for($this->ticketType)->create(['email' => 'not-a-user@example.com']);
        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);

        $this->assertNull($this->reservedTicket->purchasedTicket);

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => $user->email, // Must have a user_id to create a purchased ticket
        ]);

        $response->assertStatus(200);
        $this->assertModelExists($response->baseResponse->original->purchasedTicket);
    }

    public function test_reserved_ticket_update_call_with_purchased_ticket_returns_an_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(403);
    }

    public function test_reserved_ticket_update_call_with_matching_email_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->reservedTicket = $this->ticketType->reservedTickets()->doesntHave('purchasedTicket')->first();

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'reserved_ticket' => $this->reservedTicket->id]);

        $this->assertNotEquals($user->id, $this->reservedTicket->user_id);

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)->assertJsonPath('data.user_id', $user->id);
    }

    public function test_reserved_ticket_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->assertNull(User::where('email', 'notauser@example.com')->first());

        // Bad email
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'not_a_email',
        ]);

        $response->assertStatus(422);

        //Bad expiration_date
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'expiration_date' => 'bad date',
        ]);

        $response->assertStatus(422);
    }

    public function test_reserved_ticket_update_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('ticketTypes.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(403);
    }

    public function test_reserved_ticket_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(401);
    }
}
