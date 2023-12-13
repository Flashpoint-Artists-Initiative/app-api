<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class LoginTest extends ApiRouteTestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public string $routeName = 'login';

    public function test_logging_in_returns_a_successful_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function test_logging_in_returns_valid_credentials(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertJson(function (AssertableJson $json) {
            return $json->where('token_type', 'bearer')
                ->hasAll(['access_token', 'expires_in']);
        });
    }

    public function test_logging_in_with_invalid_credentials_returns_a_failed_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422);
    }

    public function test_logging_in_with_invalid_credentials_returns_an_email_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json->has('errors.email')->etc());
    }

    public function test_logging_in_without_a_password_returns_a_password_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json->has('errors.password')->etc());
    }
}
