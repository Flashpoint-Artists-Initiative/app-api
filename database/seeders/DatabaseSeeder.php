<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use App\Models\Ticketing\Waiver;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        $event = Event::factory()->offset('+3 months')->active()->create(['name' => 'Future Active Event']);
        $pastEvent = Event::factory()->offset('-3 months')->create(['name' => 'Past Event']);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->call(AddTicketsToEventSeeder::class, parameters: ['event' => $event]);
        $this->call(AddArtProjectsToEventSeeder::class, parameters: ['event' => $event]);

        $waiver = Waiver::factory()->for($event)->create();
        Waiver::factory()->for($event)->minorWaiver()->create();
        CompletedWaiver::factory()->for($waiver)->count(3)->create();
        CompletedWaiver::factory()->for($waiver)->for($user)->create();

        $ticketType = TicketType::factory()->for($event)->create();
        TicketType::factory()->for($event)->notTransferable()->create();
        TicketType::factory()->for($event)->inactive()->trashed()->create();
        TicketType::factory()->for($event)->inactive()->create();

        $pastTicketType = TicketType::factory()->for($pastEvent)->create();

        $reservedTicket = ReservedTicket::factory()->for($ticketType)->for($user)->create();
        ReservedTicket::factory()->for($ticketType)->for($user)->create();
        PurchasedTicket::factory()->for($ticketType)->for($user)->create();
        PurchasedTicket::factory()->for($reservedTicket)->for($ticketType)->for($user)->create();

        PurchasedTicket::factory()->for($pastTicketType)->for($user)->create();
    }
}
