<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\PurchasedTickets;

use App\Enums\RolesEnum;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Database\Seeders\Testing\EventSeeder;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class PurchasedTicketShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventSeeder::class;

    public string $routeName = 'api.ticket-types.purchased-tickets.show';

    public array $routeParams = ['ticket_type' => 1, 'purchased_ticket' => 1];

    protected TicketType $ticketType;

    protected PurchasedTicket $purchasedTicket;

    protected User $ticketUser;

    public function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('purchasedTickets')->active()->first();
        $this->purchasedTicket = $this->ticketType->purchasedTickets()->first();
        $this->ticketUser = $this->purchasedTicket->user;

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'purchased_ticket' => $this->purchasedTicket->id]);
    }

    public function test_purchased_ticket_view_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_purchased_ticket_view_call_without_permission_returns_error(): void
    {
        $user = User::factory()->create([
            'email' => 'newuser@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse($user->can('purchasedTickets.view'));

        $this->assertNotEquals($this->ticketUser->id, $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_purchased_ticket_view_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->assertTrue($user->can('purchasedTickets.view'));
        $this->assertNotEquals($this->ticketUser->id, $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_purchased_ticket_view_call_to_own_id_without_permission_is_successful(): void
    {
        $this->assertFalse($this->ticketUser->can('purchasedTickets.view'));

        $response = $this->actingAs($this->ticketUser)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.user_id', $this->ticketUser->id);
    }

    public function test_purchased_ticket_view_call_with_event_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'event',
        ]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.event'));
    }

    public function test_purchased_ticket_view_call_with_user_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'user',
        ]);
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.user'));
    }

    public function test_purchased_ticket_view_call_with_reserved_ticket_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->purchasedTicket = $this->ticketType->purchasedTickets()->has('reservedTicket')->first();
        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'reservedTicket',
        ]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_ticket'));
    }
}
