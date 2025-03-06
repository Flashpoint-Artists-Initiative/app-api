<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class RegisterTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'register';

    #[Test]
    public function registering_with_valid_data_returns_a_successful_response(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'birthday' => '2000-01-01',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function registering_without_valid_data_returns_validation_errors(): void
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.legal_name', 'errors.email', 'errors.password']);
            });
    }

    #[Test]
    public function registering_with_existing_email_returns_validation_errors(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'regular@example.com',
            'password' => 'password',
            'birthday' => '2000-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $json) {
                return $json->hasAll(['message', 'errors.email']);
            });
    }

    #[Test]
    public function registering_when_logged_in_returns_error(): void
    {
        $user = User::findOrFail(1);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'birthday' => '2000-01-01',
        ]);

        $response->assertStatus(400);
    }
}
