<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class ResetPasswordTest extends ApiRouteTestCase
{
    public string $routeName = 'password.update';

    public array $routeParams = [''];

    public function test_reset_password_call_with_valid_data_returns_a_successful_response(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $token = Password::createToken(User::first());

        Event::fake();

        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('newpassword', User::first()->password));

        Event::assertDispatched(PasswordReset::class);
    }

    public function test_reset_password_call_with_missing_data_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, []);
        // dd($response->getContent());

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token', 'errors.password']));
    }

    public function test_reset_password_call_with_invalid_email_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, ['email' => 'invalid_email']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_reset_password_call_with_invalid_password_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, ['password' => 'short']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.password']));
    }

    public function test_reset_password_call_with_invalid_password_confirmation_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, ['password' => 'newpassword', 'password_confirmation' => 'doesnt_match']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.password']));
    }

    public function test_reset_password_call_with_invalid_token_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => 'bad_token',
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token']));
    }

    public function test_reset_password_call_without_matching_user_email_returns_an_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);
        $token = Password::createToken(User::first());

        $response = $this->postJson($this->endpoint, [
            'email' => 'not_a_user@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_reset_password_call_with_different_user_email_returns_an_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        User::factory()->create([
            'email' => 'test-two@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $token = Password::createToken(User::first());

        $response = $this->postJson($this->endpoint, [
            'email' => 'test-two@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.token']));
    }

    public function test_reset_password_call_while_logged_in_returns_an_error(): void
    {
        $this->seed();

        $user = User::find(1);
        $response = $this->actingAs($user)->postJson($this->endpoint, ['email' => $user->email]);

        $response->assertStatus(400);
    }
}