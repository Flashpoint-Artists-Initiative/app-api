<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::inRandomOrder()->take(3)->get();

        foreach ($events as $event) {
            TicketType::factory()->for($event)->count(3)->create();
            TicketType::factory()->for($event)->inactive()->trashed()->create();
            TicketType::factory()->for($event)->inactive()->create();
            TicketType::factory()->for($event)->zeroQuantity()->create();
            TicketType::factory()->for($event)->free()->create();
            TicketType::factory()->for($event)->onSaleInFuture()->create();
            TicketType::factory()->for($event)->onSaleInPast()->create();
            TicketType::factory()->for($event)->onSaleInPast()->trashed()->create();
        }
    }
}
