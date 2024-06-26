<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PermissionPopulateCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    /**
     * A basic unit test example.
     */
    public function test_permission_populate_command_returns_zero(): void
    {
        $result = $this->artisan('permission:populate');

        $this->assertIsNotInt($result);

        $result->assertExitCode(0);
    }
}
