<?php

namespace Database\Seeders\Testing;

use App\Models\Event;
use App\Models\Volunteering\ShiftType;
use Illuminate\Database\Seeder;

class ShiftTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(TeamSeeder::class);

        $events = [
            Event::has('teams')->where('active', true)->first(),
            Event::has('teams')->where('active', false)->first(),
        ];

        foreach ($events as $event) {
            foreach ([true, false] as $val) {
                $team = $event->teams()->where('active', $val)->first();
                ShiftType::factory()->for($team)->count(2)->create();
            }
        }
    }
}
