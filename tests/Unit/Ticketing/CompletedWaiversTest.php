<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;
use App\Models\User;
use Database\Seeders\Testing\WaiverSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletedWaiversTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public string $seeder = WaiverSeeder::class;

    public function test_relations(): void
    {
        $user = User::first();
        $waiver = Waiver::first();
        $completedWaiver = CompletedWaiver::create(['user_id' => $user->id, 'waiver_id' => $waiver->id]);

        $this->assertEquals($completedWaiver->user->id, $user->id);
        $this->assertEquals($completedWaiver->waiver->id, $waiver->id);
    }
}
