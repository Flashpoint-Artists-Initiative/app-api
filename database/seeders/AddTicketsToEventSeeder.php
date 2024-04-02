<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Seeder;

class AddTicketsToEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(Event $event): void
    {
        $ticketType = TicketType::factory()->for($event)->create();
        // ReservedTicket::factory()->forUser()->for($ticketType)->count(3)->create();
        $reservedTicket = ReservedTicket::factory()->forUser()->for($ticketType)->create();

        PurchasedTicket::factory()->forUser()->for($ticketType)->create();
        PurchasedTicket::factory()->for($reservedTicket->user)->for($reservedTicket)->for($ticketType)->create();

    }
}
