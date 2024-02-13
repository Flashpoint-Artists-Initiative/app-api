<?php

namespace Database\Seeders\Testing;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\TicketTransfer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new \Exception('Testing seeders can only be used during testing');
        }

        $this->call(EventWithMultipleTicketTypesSeeder::class);

        $user = User::has('purchasedTickets')->first();

        $transfer = TicketTransfer::factory()->for($user)->create();
        $transfer->purchasedTickets()->attach($user->purchasedTickets->first());

        $transfer = TicketTransfer::factory()->create();
        $transfer->purchasedTickets()->attach(PurchasedTicket::first());

    }
}
