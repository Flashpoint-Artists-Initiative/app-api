<?php

namespace Database\Seeders\Testing;

use App\Models\Event;
use App\Models\Volunteering\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ShiftTypeSeeder::class);

        $activeEvent = Event::where('active', true)->has('teams')->first();
        $inactiveEvent = Event::where('active', false)->has('teams')->first();

        $teams = [
            'doubleActiveTeam' => $activeEvent->teams()->where('active', true)->has('shiftTypes')->first(),
            'activeEventInactiveTeam' => $activeEvent->teams()->where('active', false)->has('shiftTypes')->first(),
            'inactiveEventActiveTeam' => $inactiveEvent->teams()->where('active', true)->has('shiftTypes')->first(),
            'doubleInactiveTeam' => $inactiveEvent->teams()->where('active', false)->has('shiftTypes')->first(),
        ];

        foreach ($teams as $k => $team) {
            $shiftType = $team->shiftTypes()->first();
            Shift::factory()->for($shiftType)->count(4)->create();
        }
    }
}
