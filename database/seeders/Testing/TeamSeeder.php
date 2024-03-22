<?php

namespace Database\Seeders\Testing;

use App\Models\Event;
use App\Models\Volunteering\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(EventSeeder::class);

        $event = Event::where('active', true)->first();
        $inactive = Event::where('active', false)->first();

        Team::factory()->for($event)->count(4)->create();
        Team::factory()->for($event)->inactive()->create();
        Team::factory()->for($event)->trashed()->create();

        Team::factory()->for($inactive)->create();
        Team::factory()->for($inactive)->inactive()->create();
        Team::factory()->for($inactive)->trashed()->create();
    }
}
