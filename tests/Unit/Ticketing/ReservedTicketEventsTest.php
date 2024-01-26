<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Database\Seeders\Testing\EventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservedTicketEventsTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public string $seeder = EventSeeder::class;

    public function test_update_reserved_ticket_with_purchased_ticket_fails(): void
    {
        $user = User::first();
        $reservedTicket = ReservedTicket::has('purchasedTicket')->first();

        $reservedTicket->expiration_date = now()->addMinute();
        $success = $reservedTicket->save();

        $this->assertFalse($success);
    }

    public function test_update_reserved_ticket_without_purchased_ticket_succeeds(): void
    {
        $user = User::first();
        $reservedTicket = ReservedTicket::doesntHave('purchasedTicket')->first();

        $reservedTicket->expiration_date = now()->addMinute();
        $success = $reservedTicket->save();

        $this->assertTrue($success);
    }

    public function test_delete_reserved_ticket_with_purchased_ticket_fails(): void
    {
        $user = User::first();
        $reservedTicket = ReservedTicket::has('purchasedTicket')->first();

        $reservedTicket->delete();

        $this->assertModelExists($reservedTicket);
    }

    public function test_delete_reserved_ticket_without_purchased_ticket_succeeds(): void
    {
        $user = User::first();
        $reservedTicket = ReservedTicket::doesntHave('purchasedTicket')->first();

        $reservedTicket->delete();

        $this->assertModelMissing($reservedTicket);
    }
}
