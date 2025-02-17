<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CompletedWaiversTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    #[Test]
    public function relations(): void
    {
        $user = User::firstOrFail();
        $waiver = Waiver::firstOrFail();
        $completedWaiver = CompletedWaiver::create(['user_id' => $user->id, 'waiver_id' => $waiver->id]);

        $this->assertEquals($completedWaiver->user->id, $user->id);
        $this->assertEquals($completedWaiver->waiver->id, $waiver->id);
    }
}
