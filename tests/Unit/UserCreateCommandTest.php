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

        $this->artisan('user:create "Test User" "test@example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin" -y')->assertSuccessful();

        $this->assertGreaterThan($count, User::count());

        $user = User::latest('id')->first();
        $this->assertEquals($user->email, 'test@example.com');
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_create_command_with_prompts_returns_success(): void
    {

        $this->artisan('user:create')
            ->expectsQuestion('Legal Name', 'Test User')
            ->expectsQuestion('Email', 'test@example.com')
            ->expectsQuestion('Birthday', '2000-01-02')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Preferred Name', 'My Name')
            ->expectsQuestion('Role', 'admin')
            ->expectsTable(['Field', 'Input'], [
                ['Legal Name', 'Test User'],
                ['Email', 'test@example.com'],
                ['Birthday', '2000-01-02'],
                ['Password', 'password'],
                ['Preferred Name', 'My Name'],
                ['Role', 'admin'],
            ])
            ->expectsConfirmation('Add user?', 'yes')
            ->assertSuccessful();

        $user = User::latest('id')->first();
        $this->assertEquals($user->email, 'test@example.com');
        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_user_create_with_provided_input_and_no_confirmation_returns_one(): void
    {
        $this->artisan('user:create "Test User" "test@example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin"')
            ->expectsConfirmation('Add user?')
            ->assertExitCode(1);
    }

    public function test_user_create_with_duplicate_email_returns_two(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->artisan('user:create "Test User" "test@example.com" "1980-01-02", "password" --preferred_name="My Name" --role="admin" -y')
            ->assertExitCode(2);
    }
}
