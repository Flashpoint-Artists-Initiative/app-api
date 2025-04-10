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
        $zeroPriceTicketType = TicketType::inRandomOrder()->where('price', 0)->first();
        $ticketTypes->push($zeroPriceTicketType);

        foreach ($ticketTypes as $ticketType) {
            ReservedTicket::factory()->for($ticketType)->withEmail()->count(2)->create();
            ReservedTicket::factory()->for($ticketType)->forUser()->count(2)->create();
            ReservedTicket::factory()->for($ticketType)->withEmail()->expirationDateInPast()->count(2)->create();
        }

    }
}
