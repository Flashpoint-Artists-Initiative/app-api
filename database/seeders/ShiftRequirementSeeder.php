<?php

namespace Database\Seeders;

use App\Models\Volunteering\Requirement;
use Illuminate\Database\Seeder;

class ShiftRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Requirement::factory()->count(3)->create();
        Requirement::factory()->trashed()->create();
    }
}
