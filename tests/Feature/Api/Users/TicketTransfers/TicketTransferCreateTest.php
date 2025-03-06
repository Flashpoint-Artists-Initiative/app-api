<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Users\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferCreateTest extends ApiRouteTestCase
{
    protected bool $seed = true;

    public string $routeName = 'api.users.ticket-transfers.store';

    public array $routeParams = ['user' => 1];

    public User $user;

    public ReservedTicket $reservedTicket;

    public PurchasedTicket $purchasedTicket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->purchasedTicket = PurchasedTicket::factory()->for($this->user)->create();
        $this->reservedTicket = ReservedTicket::factory()->for($this->user)->create();

        $this->addEndpointParams(['user' => $this->user->id]);
    }

    #[Test]
    public function ticket_transfer_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $admin = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function ticket_transfer_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // No data
        $response = $this->actingAs($user)->postJson($this->endpoint, [
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'bad_email',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => 'one',
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => 'one',
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - not an int
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => ['one'],
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - invalid
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [1000],
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - duplicate
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id, $this->purchasedTicket->id],
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - not an int
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => ['one'],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - invalid
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => [1000],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - duplicate
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
            'reserved_tickets' => [$this->reservedTicket->id, $this->reservedTicket->id],
        ]);

        $response->assertStatus(422);

        // Change the endpoint to the admin user, then try using the same ticket ids
        $this->buildEndpoint(params: ['user' => $user->id]);

        // Bad purchased_tickets
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->purchasedTicket->id],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$this->reservedTicket->id],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function ticket_transfer_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->where('id', '!=', $this->user->id)->firstOrFail();

        $this->assertFalse($user->can('ticketTypes.create'));
        $this->assertFalse($user->id == $this->user->id);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function ticket_transfer_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function ticket_transfer_create_call_with_bad_purchased_ticket_data_returns_validation_error(): void
    {
        $nonTransferableTicket = PurchasedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => TicketType::where('transferable', false)->firstOrFail()->id,
        ]);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$nonTransferableTicket->id],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function ticket_transfer_create_call_with_bad_reserved_ticket_data_returns_validation_error(): void
    {
        $admin = User::role(RolesEnum::Admin)->firstOrFail();

        $nonTransferableTicket = ReservedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => TicketType::where('transferable', false)->firstOrFail()->id,
        ]);
        $unpurchaseableTicket = ReservedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => TicketType::where('sale_end_date', '<=', now())->firstOrFail()->id,
        ]);
        $withPurchasedTicket = ReservedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => TicketType::query()->available()->firstOrFail()->id,
        ]);
        // Associated purchasedTicket for $withPurchasedTicket
        PurchasedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => $withPurchasedTicket->ticket_type_id,
            'reserved_ticket_id' => $withPurchasedTicket->id,
        ]);

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$nonTransferableTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$unpurchaseableTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$withPurchasedTicket->id],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function ticket_transfer_create_call_with_duplicate_data_returns_validation_error(): void
    {
        $admin = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
            'reserved_tickets' => [$this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id],
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($admin)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id],
        ]);

        $response->assertStatus(422);
    }
}
