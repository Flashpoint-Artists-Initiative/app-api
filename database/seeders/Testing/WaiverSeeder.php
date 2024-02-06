<?php

namespace Database\Seeders\Testing;

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
        if (! app()->environment('testing')) {
            throw new \Exception('Testing seeders can only be used during testing');
        }

        $this->call(UserSeeder::class);

        $event = Event::factory()->offset('+3 months')->active()->create(['name' => 'Future Active Event']);
        Waiver::factory()->for($event)->create();
        Waiver::factory()->for($event)->minorWaiver()->create();
        CompletedWaiver::factory()->count(3)->create();
    }
}
