<?php

namespace Database\Seeders\Testing;

use App\Models\Volunteering\Requirement;
use Illuminate\Database\Seeder;

class ShiftRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(ShiftTypeSeeder::class);

        Requirement::factory()->count(3)->create();
        Requirement::factory()->trashed()->create();
    }
}
