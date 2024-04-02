<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;
use Illuminate\Database\Seeder;

class WaiverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $event = Event::factory()->offset('+3 months')->active()->create(['name' => 'Future Active Event']);
        Waiver::factory()->for($event)->create();
        Waiver::factory()->for($event)->minorWaiver()->create();
        CompletedWaiver::factory()->count(3)->create();
    }
}
