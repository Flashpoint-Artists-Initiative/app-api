<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservedTicketEventsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    #[Test]
    public function update_reserved_ticket_with_purchased_ticket_fails(): void
    {
        $user = User::firstOrFail();
        $reservedTicket = ReservedTicket::has('purchasedTicket')->firstOrFail();

        $reservedTicket->expiration_date = now()->addMinute();
        $success = $reservedTicket->save();

        $this->assertFalse($success);
    }

    #[Test]
    public function update_reserved_ticket_without_purchased_ticket_succeeds(): void
    {
        $user = User::firstOrFail();
        $reservedTicket = ReservedTicket::doesntHave('purchasedTicket')->firstOrFail();

        $reservedTicket->expiration_date = now()->addMinute();
        $success = $reservedTicket->save();

        $this->assertTrue($success);
    }

    #[Test]
    public function delete_reserved_ticket_with_purchased_ticket_fails(): void
    {
        $user = User::firstOrFail();
        $reservedTicket = ReservedTicket::has('purchasedTicket')->firstOrFail();

        $reservedTicket->delete();

        $this->assertModelExists($reservedTicket);
    }

    #[Test]
    public function delete_reserved_ticket_without_purchased_ticket_succeeds(): void
    {
        $user = User::firstOrFail();
        $reservedTicket = ReservedTicket::doesntHave('purchasedTicket')->firstOrFail();

        $reservedTicket->delete();

        $this->assertModelMissing($reservedTicket);
    }
}
