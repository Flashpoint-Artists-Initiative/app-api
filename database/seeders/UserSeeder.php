<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->unverified()->create([
            'legal_name' => 'Unverified User',
            'email' => 'unverified@example.com',
            'password' => 'unverified',
        ]);

        User::factory()->create([
            'legal_name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => 'regular',
        ]);

        User::factory()->create([
            'legal_name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => 'superadmin',
        ])->assignRole(RolesEnum::SuperAdmin);

        User::factory()->create([
            'legal_name' => 'Regular Admin User',
            'email' => 'admin@example.com',
            'password' => 'admin',
        ])->assignRole(RolesEnum::Admin);

        User::factory()->create([
            'legal_name' => 'Event Manager User',
            'email' => 'eventmanager@example.com',
            'password' => 'eventmanager',
        ])->assignRole(RolesEnum::EventManager);
    }
}
