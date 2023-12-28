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
        Event::factory()->offset('-5 years')->create();
        Event::factory()->offset('+2 years')->create();

        Event::factory()->offset('-2 years')->active()->create();
        Event::factory()->offset('+3 years')->active()->create();

        Event::factory()->offset('-1 year')->trashed()->create();
        Event::factory()->active()->trashed()->create();
    }
}
