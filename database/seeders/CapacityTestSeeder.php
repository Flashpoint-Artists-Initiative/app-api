<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Models\Ticketing\Order;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use App\Models\Volunteering\Team;
use Illuminate\Database\Seeder;

class CapacityTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory()->count(50000)->create();
        echo "Orders\n";
        Order::factory()->count(10000)->create();
        echo "Purchased Tickets\n";
        PurchasedTicket::factory()->count(1000)->create();
        echo "Reserved Tickets\n";
        ReservedTicket::factory()->count(20000)->create();
        echo "Ticket Types\n";
        TicketType::factory()->count(100)->create();
        echo "Events\n";
        Event::factory()->count(100)->create();
        echo "Art Projects\n";
        ArtProject::factory()->count(10000)->create();
        echo "Teams\n";
        Team::factory()->count(10000)->create();
        echo "Shifts\n";
        Shift::factory()->count(10000)->create();
        echo "Shift Types\n";
        ShiftType::factory()->count(10000)->create();
    }
}
