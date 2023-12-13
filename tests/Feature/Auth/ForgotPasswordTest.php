<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class ForgotPasswordTest extends ApiRouteTestCase
{
    public string $routeName = 'password.email';

    public function test_forgot_password_call_with_valid_data_returns_a_successful_response(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, ['email' => 'test@example.com']);

        $response->assertStatus(200);
    }

    public function test_forgot_password_email_is_sent(): void
    {
        $this->seed();

        $email = 'test@example.com';

        /** @var \Illuminate\Mail\Transport\ArrayTransport */
        $emailTransport = app('mailer')->getSymfonyTransport();

        $this->assertCount(0, $emailTransport->messages(), 'Start with 0 messages sent');

        $response = $this->postJson($this->endpoint, ['email' => $email]);

        $response->assertStatus(200);

        $this->assertCount(1, $emailTransport->messages(), 'Now one message is sent');

        $sentEmail = $emailTransport->messages()->pop();

        $this->assertEquals($sentEmail->getOriginalMessage()->getTo()[0]->getAddress(), $email, 'Email was sent to the correct address');
    }

    public function test_multiple_forgot_password_calls_too_fast_returns_an_error_response(): void
    {
        $this->seed();

        $this->postJson($this->endpoint, ['email' => 'test@example.com']);
        $response = $this->postJson($this->endpoint, ['email' => 'test@example.com']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_forgot_password_call_with_missing_data_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, []);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_forgot_password_call_with_invalid_email_returns_a_validation_error(): void
    {
        $this->seed();

        $response = $this->postJson($this->endpoint, ['email' => 'invalid_email']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_forgot_password_call_without_matching_user_email_returns_an_error(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->postJson($this->endpoint, ['email' => 'not-a-user@example.com']);

        $response->assertStatus(422)
            ->assertJson(fn (AssertableJson $json) => $json->hasAll(['message', 'errors.email']));
    }

    public function test_forgot_password_call_while_logged_in_returns_an_error(): void
    {
        $this->seed();

        $user = User::find(1);
        $response = $this->actingAs($user)->postJson($this->endpoint, ['email' => $user->email]);

        $response->assertStatus(400);
    }
}
