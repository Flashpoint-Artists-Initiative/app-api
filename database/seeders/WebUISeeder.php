<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;
use Illuminate\Database\Seeder;

class WebUISeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);

        $event = Event::factory()->offset('+3 months')->active()->create(['name' => 'Future Active Event']);

        $this->call(AddTicketsToEventSeeder::class, parameters: ['event' => $event]);

        $waiver = Waiver::factory()->for($event)->create();
        Waiver::factory()->for($event)->minorWaiver()->create();
        CompletedWaiver::factory()->for($waiver)->count(3)->create();
    }
}
