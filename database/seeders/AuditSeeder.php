<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use OwenIt\Auditing\Models\Audit;

class AuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Audit::create([
            'event' => 'created',
            'auditable_type' => 'event',
            'auditable_id' => 1,
            'old_values' => [],
            'new_values' => [],
        ]);

        Audit::create([
            'user_id' => 1,
            'user_type' => 'user',
            'event' => 'updated',
            'auditable_type' => 'event',
            'auditable_id' => 1,
            'old_values' => [],
            'new_values' => [],
        ]);
    }
}
