<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EventSeeder::class,
            TicketTypeSeeder::class,
            ReservedTicketSeeder::class,
            PurchasedTicketSeeder::class,
            TicketTransferSeeder::class,
            OrderSeeder::class,
            WaiverSeeder::class,
            TeamSeeder::class,
            ShiftTypeSeeder::class,
            ShiftSeeder::class,
            ShiftRequirementSeeder::class,
        ]);
    }
}
