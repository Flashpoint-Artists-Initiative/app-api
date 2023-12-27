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
        Event::factory(2)->create();
        Event::factory(2)->active()->create();
        Event::factory(2)->trashed()->create();
    }
}
