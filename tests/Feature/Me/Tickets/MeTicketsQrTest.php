<?php

declare(strict_types=1);

namespace Tests\Feature\Me\Tickets;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\Testing\EventSeeder;
use Tests\ApiRouteTestCase;

class MeTicketsQrTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventSeeder::class;

    public string $routeName = 'api.me.tickets.qr';

    public function test_me_tickets_qr_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_tickets_qr_call_as_user_returns_success(): void
    {
        $user = User::has('purchasedTickets')->first();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_me_tickets_qr_call_with_event_id_returns_success(): void
    {
        $user = User::has('purchasedTickets')->first();
        $ticket = $user->purchasedTickets->first();
        $eventId = $ticket->ticketType->event_id;

        $this->addEndpointParams(['event_id' => $eventId]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }

    public function test_me_tickets_qr_call_with_invalid_event_id_returns_error(): void
    {
        $user = User::has('purchasedTickets')->first();
        $ticket = $user->purchasedTickets->first();
        $eventId = $ticket->ticketType->event_id;
        $otherEventId = Event::where('id', '!=', $eventId)->doesntHave('purchasedTickets')->first()->id;

        // non-int event_id
        $this->addEndpointParams(['event_id' => 'abc']);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(422);

        // Non-event event_id
        $this->addEndpointParams(['event_id' => 9999]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(422);

        // Correct event_id, but no matching ticket
        $this->addEndpointParams(['event_id' => $otherEventId]);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(404);
    }
}
