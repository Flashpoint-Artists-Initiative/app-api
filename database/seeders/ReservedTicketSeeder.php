<?php

namespace Database\Seeders;

use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Seeder;

class ReservedTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ticketTypes = TicketType::inRandomOrder()->take(5)->get();

        foreach ($ticketTypes as $ticketType) {
            ReservedTicket::factory()->for($ticketType)->withEmail()->count(15)->create();
            ReservedTicket::factory()->for($ticketType)->forUser()->count(15)->create();
            ReservedTicket::factory()->for($ticketType)->withEmail()->expirationDateInPast()->count(5)->create();
        }
    }
}
