<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $event = Event::factory()->offset('+3 months')->active()->create(['name' => 'Future Active Event']);
        Event::factory()->offset('-5 years')->create(['name' => 'Past Inactive Event']);

        Event::factory()->offset('+2 years')->create(['name' => 'Future Inactive Event']);

        Event::factory()->offset('-2 years')->active()->create(['name' => 'Past Active Event']);
        Event::factory()->offset('+3 years')->active()->create(['name' => 'Far Future Active Event']);

        Event::factory()->offset('-1 year')->trashed()->create(['name' => 'Deleted Inactive Event']);
        Event::factory()->active()->trashed()->create(['name' => 'Deleted Active Event']);

        $this->call(AddTicketsToEventSeeder::class, parameters: ['event' => $event]);
    }
}
