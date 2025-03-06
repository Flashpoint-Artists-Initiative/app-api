<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ResetPasswordTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'password.update';

    #[Test]
    public function reset_password_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $token = Password::createToken($user);

        Event::fake();

        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertTrue(Hash::check('newpassword', $user->password));

        Event::assertDispatched(PasswordReset::class);
    }

    #[Test]
    public function reset_password_call_with_missing_data_returns_a_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token', 'errors.password']));
    }

    #[Test]
    public function reset_password_call_with_invalid_email_returns_a_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, ['email' => 'invalid_email']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    #[Test]
    public function reset_password_call_with_invalid_password_returns_a_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, ['password' => 'short']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.password']));
    }

    #[Test]
    public function reset_password_call_with_invalid_token_returns_a_validation_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'regular@example.com',
            'password' => 'newpassword',
            'token' => 'bad_token',
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token']));
    }

    #[Test]
    public function reset_password_call_without_matching_user_email_returns_an_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);
        $token = Password::createToken(User::firstOrFail());

        $response = $this->postJson($this->endpoint, [
            'email' => 'not_a_user@example.com',
            'password' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    #[Test]
    public function reset_password_call_with_different_user_email_returns_an_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        User::factory()->create([
            'email' => 'test-two@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $token = Password::createToken(User::firstOrFail());

        $response = $this->postJson($this->endpoint, [
            'email' => 'test-two@example.com',
            'password' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token']));
    }

    #[Test]
    public function reset_password_call_while_logged_in_returns_an_error(): void
    {
        $user = User::findOrFail(1);
        $response = $this->actingAs($user)->postJson($this->endpoint, ['email' => $user->email]);

        $response->assertStatus(400);
    }
}
