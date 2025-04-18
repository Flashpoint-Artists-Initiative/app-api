<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Me\TicketTransfers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTransferCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.ticket-transfers.store';

    public array $routeParams = [];

    public User $user;

    public ReservedTicket $reservedTicket;

    public PurchasedTicket $purchasedTicket;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::doesntHave('purchasedTickets')->doesntHave('reservedTickets')->firstOrFail();
        $this->purchasedTicket = PurchasedTicket::factory()->for($this->user)->create();
        $this->reservedTicket = ReservedTicket::factory()->for($this->user)->create();
    }

    #[Test]
    public function me_ticket_transfer_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
            'reserved_tickets' => [$this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id],
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function me_ticket_transfer_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $purchasedTicketId = $this->user->purchasedTickets->firstOrFail()->id;
        $reservedTicketId = $this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id;

        // No data
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'bad_email',
            'purchased_tickets' => [$purchasedTicketId],
            'reserved_tickets' => [$reservedTicketId],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => 'one',
            'reserved_tickets' => [$reservedTicketId],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$purchasedTicketId],
            'reserved_tickets' => 'one',
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - not an int
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => ['one'],
            'reserved_tickets' => [$reservedTicketId],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - invalid
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [1000],
            'reserved_tickets' => [$reservedTicketId],
        ]);

        $response->assertStatus(422);

        // Bad purchased_tickets.id - duplicate
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$purchasedTicketId, $purchasedTicketId],
            'reserved_tickets' => [$reservedTicketId],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - not an int
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$purchasedTicketId],
            'reserved_tickets' => ['one'],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - invalid
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$purchasedTicketId],
            'reserved_tickets' => [1000],
        ]);

        $response->assertStatus(422);

        // Bad reserved_tickets.id - duplicate
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$purchasedTicketId],
            'reserved_tickets' => [$reservedTicketId, $reservedTicketId],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function me_ticket_transfer_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_ticket_transfer_create_call_with_bad_purchased_ticket_data_returns_validation_error(): void
    {
        $nonTransferableTicket = PurchasedTicket::create([
            'user_id' => $this->user->id,
            'ticket_type_id' => TicketType::where('transferable', false)->firstOrFail()->id,
        ]);
        $otherUsersTicket = PurchasedTicket::create([
            'user_id' => $this->user->id + 1,
            'ticket_type_id' => TicketType::query()->available()->firstOrFail()->id,
        ]);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$nonTransferableTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$otherUsersTicket->id],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function me_ticket_transfer_create_call_with_bad_reserved_ticket_data_returns_validation_error(): void
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
        $otherUsersTicket = ReservedTicket::create([
            'user_id' => $this->user->id + 1,
            'ticket_type_id' => TicketType::query()->available()->firstOrFail()->id,
        ]);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$nonTransferableTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$unpurchaseableTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$withPurchasedTicket->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$otherUsersTicket->id],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function me_ticket_transfer_create_call_with_duplicate_data_returns_validation_error(): void
    {
        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
            'reserved_tickets' => [$this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id],
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'purchased_tickets' => [$this->user->purchasedTickets->firstOrFail()->id],
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($this->user)->postJson($this->endpoint, [
            'email' => 'test@test.com',
            'reserved_tickets' => [$this->user->reservedTickets()->doesntHave('purchasedTicket')->firstOrFail()->id],
        ]);

        $response->assertStatus(422);
    }
}
