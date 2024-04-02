<?php

namespace Database\Seeders;

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
        $user = User::has('purchasedTickets')->first();

        $transfer = TicketTransfer::factory()->for($user)->create();
        $transfer->purchasedTickets()->attach($user->purchasedTickets->first());

        $transfer = TicketTransfer::factory()->create();
        $transfer->purchasedTickets()->attach(PurchasedTicket::first());

    }
}
