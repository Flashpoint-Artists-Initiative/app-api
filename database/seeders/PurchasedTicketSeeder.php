<?php

namespace Database\Seeders;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use Illuminate\Database\Seeder;

class PurchasedTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservedTickets = ReservedTicket::inRandomOrder()->with('ticketType')->take(10)->get();

        foreach ($reservedTickets as $reservedTicket) {
            PurchasedTicket::factory()->for($reservedTicket)->for($reservedTicket->ticketType)->create();
            for ($i = 0; $i < fake()->randomDigit(); $i++) {
                PurchasedTicket::factory()->for($reservedTicket->user)->for($reservedTicket->ticketType)->create();
            }
        }
    }
}
