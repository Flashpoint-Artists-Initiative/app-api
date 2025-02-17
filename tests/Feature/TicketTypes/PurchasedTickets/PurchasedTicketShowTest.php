<?php

declare(strict_types=1);

namespace Tests\Feature\TicketTypes\PurchasedTickets;

use App\Enums\RolesEnum;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class PurchasedTicketShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.purchased-tickets.show';

    public array $routeParams = ['ticket_type' => 1, 'purchased_ticket' => 1];

    protected TicketType $ticketType;

    protected PurchasedTicket $purchasedTicket;

    protected User $ticketUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketType = TicketType::has('purchasedTickets')->active()->firstOrFail();
        $this->purchasedTicket = $this->ticketType->purchasedTickets()->firstOrFail();
        $this->ticketUser = $this->purchasedTicket->user;

        $this->buildEndpoint(params: ['ticket_type' => $this->ticketType->id, 'purchased_ticket' => $this->purchasedTicket->id]);
    }

    #[Test]
    public function purchased_ticket_view_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function purchased_ticket_view_call_without_permission_returns_error(): void
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

    #[Test]
    public function purchased_ticket_view_call_with_permission_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('purchasedTickets.view'));
        $this->assertNotEquals($this->ticketUser->id, $user->id);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    #[Test]
    public function purchased_ticket_view_call_to_own_id_without_permission_is_successful(): void
    {
        $this->assertFalse($this->ticketUser->can('purchasedTickets.view'));

        $response = $this->actingAs($this->ticketUser)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.user_id', $this->ticketUser->id);
    }

    #[Test]
    public function purchased_ticket_view_call_with_event_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'event',
        ]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.event'));
    }

    #[Test]
    public function purchased_ticket_view_call_with_user_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'user',
        ]);
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.user'));
    }

    #[Test]
    public function purchased_ticket_view_call_with_reserved_ticket_is_successful(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();
        $this->purchasedTicket = $this->ticketType->purchasedTickets()->has('reservedTicket')->firstOrFail();
        $this->buildEndpoint(params: [
            'ticket_type' => $this->ticketType->id,
            'purchased_ticket' => $this->purchasedTicket->id,
            'include' => 'reservedTicket',
        ]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJson(fn (AssertableJson $json) => $json->has('data.reserved_ticket'));
    }
}
