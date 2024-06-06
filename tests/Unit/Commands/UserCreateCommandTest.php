<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserCreateCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    public function test_user_create_with_provided_input_returns_success(): void
    {
        $count = User::count();

        $result = $this->artisan('user:create "Test User" "test@test-example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin" -y');
        $this->assertIsNotInt($result);

        $result->assertSuccessful()
            ->run();

        $this->assertGreaterThan($count, User::count());

        $user = User::latest('id')->firstOrFail();
        $this->assertEquals($user->email, 'test@test-example.com');
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_create_command_with_prompts_returns_success(): void
    {

        $pendingCommand = $this->artisan('user:create');

        $this->assertIsNotInt($pendingCommand);

        $pendingCommand->expectsQuestion('Legal Name', 'Test User')
            ->expectsQuestion('Email', 'test@test-example.com')
            ->expectsQuestion('Birthday', '2000-01-02')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Preferred Name', 'My Name')
            ->expectsQuestion('Role', 'admin')
            ->expectsTable(['Field', 'Input'], [
                ['Legal Name', 'Test User'],
                ['Email', 'test@test-example.com'],
                ['Birthday', '2000-01-02'],
                ['Password', 'password'],
                ['Preferred Name', 'My Name'],
                ['Role', 'admin'],
            ])
            ->expectsConfirmation('Add user?', 'yes')
            ->assertSuccessful()
            ->run();

        $user = User::latest('id')->firstOrFail();
        $this->assertEquals($user->email, 'test@test-example.com');
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_create_with_provided_input_and_no_confirmation_returns_one(): void
    {
        $pendingCommand = $this->artisan('user:create "Test User" "test@test-example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin"');

        $this->assertIsNotInt($pendingCommand);

        $pendingCommand->expectsConfirmation('Add user?')
            ->assertExitCode(1);
    }

    public function test_user_create_with_duplicate_email_returns_two(): void
    {
        User::factory()->create(['email' => 'test@test-example.com']);

        $pendingCommand = $this->artisan('user:create "Test User" "test@test-example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin" -y');

        $this->assertIsNotInt($pendingCommand);

        $pendingCommand->assertExitCode(2);
    }
}
