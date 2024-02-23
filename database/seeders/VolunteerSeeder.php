<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Models\Volunteering\ShiftType;
use App\Models\Volunteering\Team;
use Illuminate\Database\Seeder;

class VolunteerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(20)->create();

        Team::factory(4)
            ->has(ShiftType::factory(3)
                ->has(Shift::factory(3))
            )
            ->create();

        foreach (Shift::all() as $shift) {
            $users = User::whereBetween('id', [1, 20])->inRandomOrder()->take(rand(1, 4))->pluck('id');

            $shift->volunteers()->attach($users);
        }
    }
}
