<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $env = app()->environment();

        if ($env == 'local' || $env == 'testing') {
            \App\Models\User::factory(10)->unverified()->create();

            \App\Models\User::factory()->create([
                'legal_name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

            \App\Models\User::factory()->create([
                'legal_name' => 'Super Admin User',
                'email' => 'superadmin@example.com',
                'password' => 'superadmin',
            ])->assignRole(RolesEnum::SuperAdmin);

            \App\Models\User::factory()->create([
                'legal_name' => 'Regular Admin User',
                'email' => 'admin@example.com',
                'password' => 'admin',
            ])->assignRole(RolesEnum::Admin);
        }

        $this->call([
            EventSeeder::class,
        ]);
    }
}
