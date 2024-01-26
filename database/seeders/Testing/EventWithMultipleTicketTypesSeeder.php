<?php

namespace Database\Seeders\Testing;

use App\Models\Event;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Seeder;

class EventWithMultipleTicketTypesSeeder extends Seeder
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

        TicketType::factory()->for($event)->create();
        TicketType::factory()->for($event)->inactive()->trashed()->create();
        TicketType::factory()->for($event)->inactive()->create();
        TicketType::factory()->for($event)->zeroQuantity()->create();
        TicketType::factory()->for($event)->free()->create();
        TicketType::factory()->for($event)->onSaleInFuture()->create();
        TicketType::factory()->for($event)->onSaleInPast()->create();
        TicketType::factory()->for($event)->onSaleInPast()->trashed()->create();

        $this->call(AddTicketsToEventSeeder::class, parameters: ['event' => $event]);
    }
}
