<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionPopulateCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic unit test example.
     */
    public function test_permission_populate_command_returns_zero(): void
    {
        $this->artisan('permission:populate')->assertExitCode(0);
    }
}
