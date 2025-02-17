<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRoleCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    // public bool $seed = true;

    #[Test]
    public function user_role_with_provided_input_returns_success(): void
    {
        $user = User::factory()->create();

        $this->assertEmpty($user->roles);

        $result = $this->artisan("user:role {$user->id} admin");
        $this->assertIsNotInt($result);

        $result->assertSuccessful()
            ->run();

        $user->refresh();
        $this->assertNotEmpty($user->roles);
        $this->assertTrue($user->hasRole('admin'));
    }

    #[Test]
    public function user_role_delete_with_provided_input_returns_success(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertNotEmpty($user->roles);

        $result = $this->artisan("user:role {$user->id} admin -d");
        $this->assertIsNotInt($result);

        $result->assertSuccessful()
            ->run();

        $user->refresh();
        $this->assertEmpty($user->roles);
    }

    #[Test]
    public function user_role_with_invalid_user_returns_one(): void
    {
        $result = $this->artisan('user:role 999999 admin');

        $this->assertIsNotInt($result);
        $result->assertExitCode(1);
    }

    #[Test]
    public function user_role_with_invalid_role_returns_two(): void
    {
        $user = User::factory()->create();

        $result = $this->artisan("user:role {$user->id} fake");

        $this->assertIsNotInt($result);
        $result->assertExitCode(2);
    }
}
