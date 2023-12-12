<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public string $endpoint;

    public function setUp(): void
    {
        parent::setUp();
        $this->endpoint = action([RegisteredUserController::class, 'store'], [], false);
    }

    public function test_registering_with_valid_data_returns_a_successful_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_registering_without_valid_data_returns_validation_errors(): void
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.legal_name', 'errors.email', 'errors.password']);
            });
    }

    public function test_registering_without_password_confirmation_returns_validation_errors(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.password']);
            });
    }

    public function test_registering_with_mismatching_password_confirmation_returns_validation_errors(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.password']);
            });
    }

    public function test_registering_with_existing_email_returns_validation_errors(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.email']);
            });
    }

    public function test_registering_when_logged_in_returns_error(): void
    {
        $this->seed();

        $user = User::find(1);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong',
        ]);

        $response->assertStatus(400);
    }
}
