<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class LoginTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'login';

    #[Test]
    public function logging_in_returns_a_successful_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'regular@example.com',
            'password' => 'regular',
        ]);

        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json) {
            return $json->where('token_type', 'bearer')
                ->hasAll(['access_token', 'expires_in', 'permissions']);
        });
    }

    #[Test]
    public function logging_in_with_invalid_credentials_returns_a_failed_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'wrong@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422);

        $response->assertJson(fn (AssertableJson $json) => $json->has('errors.password')->etc());
    }

    #[Test]
    public function logging_in_without_a_password_returns_a_password_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'regular@example.com',
        ]);

        $response->assertJson(fn (AssertableJson $json) => $json->has('errors.password')->etc());
    }
}
