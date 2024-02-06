<?php

namespace Database\Seeders;

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
        Waiver::factory()->create();
        CompletedWaiver::factory()->count(3)->create();
    }
}
