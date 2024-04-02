<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\ApiRouteTestCase;

class MeUpdateTest extends ApiRouteTestCase
{
    public string $routeName = 'api.me.update';

    public array $routeParams = ['user' => 1];

    public function test_me_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::first();
        $data = $user->toArray();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
        ]);

        $response->assertStatus(200)->assertJsonPath('data.id', $user->id);

        $this->assertNotEquals($data, $response->baseResponse->original->toArray());
    }

    public function test_me_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::first();

        // Bad legal_name
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'legal_name' => null,
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'email' => 'bad_email',
            'birthday' => fake()->date(),
        ]);

        $response->assertStatus(422);

        //Bad birthday
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'birthday' => 'This is an invalid date',
        ]);

        $response->assertStatus(422);
    }

    public function test_me_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
        ]);

        $response->assertStatus(401);
    }

    public function test_me_update_call_changing_email_resets_email_verification(): void
    {
        $user = User::whereNot('email_verified_at', null)->first();

        $this->assertTrue($user->hasVerifiedEmail());

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'new_email@example.com',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.id', $user->id);

        $user->refresh();

        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_me_update_call_changing_email_sends_new_verification_email(): void
    {
        $user = User::whereNot('email_verified_at', null)->first();

        $this->assertTrue($user->hasVerifiedEmail());

        /** @var \Illuminate\Mail\Transport\ArrayTransport */
        $emailTransport = app('mailer')->getSymfonyTransport();

        $this->assertCount(0, $emailTransport->messages(), 'Start with 0 messages sent');

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'email' => 'new_email@example.com',
        ]);

        $response->assertStatus(200);

        $this->assertCount(1, $emailTransport->messages(), 'Now one message is sent');

        $email = $emailTransport->messages()->pop();
        $user->refresh();

        $this->assertEquals($email->getOriginalMessage()->getTo()[0]->getAddress(), $user->email, 'Email was sent to the correct address');
    }

    public function test_me_update_call_changing_password_hashes_correctly(): void
    {
        $user = User::first();
        $newPassword = 'new_password';
        $oldHash = $user->password;

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'password' => $newPassword,
        ]);

        $response->assertStatus(200);
        $user->refresh();

        $this->assertNotEquals($oldHash, $user->password);
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }
}
