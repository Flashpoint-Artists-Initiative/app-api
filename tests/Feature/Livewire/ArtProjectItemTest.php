<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\ArtProjectItem;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArtProjectItemTest extends TestCase
{
    #[Test]
    public function renders_successfully(): void
    {
        Livewire::test(ArtProjectItem::class)
            ->assertStatus(200);
    }
}
