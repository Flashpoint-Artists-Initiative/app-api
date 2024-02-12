<?php

namespace Database\Seeders\Testing;

use App\Models\Event;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Seeder;

class UserWithTicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new \Exception('Testing seeders can only be used during testing');
        }

        $event = Event::factory()->create();
        $ticketType = TicketType::factory()->for($event)->create();
        $reservedTicket = ReservedTicket::factory()->forUser()->for($ticketType)->create();

        ReservedTicket::factory()->for($reservedTicket->user)->for($ticketType)->create();

        PurchasedTicket::factory()->for($reservedTicket->user)->for($ticketType)->create();
        PurchasedTicket::factory()->for($reservedTicket->user)->for($reservedTicket)->for($ticketType)->create();

    }
}
