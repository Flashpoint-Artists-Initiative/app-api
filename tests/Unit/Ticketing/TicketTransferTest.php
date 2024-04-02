<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class TicketTransferTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    public function test_second_completion(): void
    {
        $transfer = TicketTransfer::first();
        $ticket = $transfer->purchasedTickets->first();
        $firstUser = $ticket->user;
        $secondUser = User::factory()->create(['email' => $transfer->recipient_email]);

        $this->assertEquals(0, $transfer->completed);
        $this->assertEquals($firstUser->id, $ticket->user_id);

        $transfer->complete();
        $ticket->refresh();
        $transfer->refresh();

        $this->assertEquals(1, $transfer->completed);
        $this->assertEquals($secondUser->id, $ticket->user_id);

        // After transfer, move the ticket back to the original user
        $ticket->user_id = $firstUser->id;
        $ticket->saveQuietly();

        $transfer->complete();
        $ticket->refresh();
        $transfer->refresh();

        // Check that the ticket didn't change owners again
        $this->assertEquals(1, $transfer->completed);
        $this->assertEquals($firstUser->id, $ticket->user_id);
    }

    public function test_recipient_relation(): void
    {
        $transfer = TicketTransfer::first();
        $recipient = User::factory()->create(['email' => $transfer->recipient_email]);

        $this->assertEquals($transfer->recipient->id, $recipient->id);
    }

    public function test_update_fails(): void
    {
        $transfer = TicketTransfer::first();
        $email = $transfer->recipient_email;
        $newEmail = 'newEmail@test.com';

        $this->assertNotEquals($email, $newEmail);

        $success = $transfer->update(['recipient_email' => $newEmail]);

        $this->assertFalse($success);

        $transfer->refresh();

        $this->assertNotEquals($transfer->recipient_email, $newEmail);
    }
}
