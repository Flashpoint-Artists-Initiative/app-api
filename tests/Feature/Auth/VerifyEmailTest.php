<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\ApiRouteTestCase;

class VerifyEmailTest extends ApiRouteTestCase
{
    use RefreshDatabase;

    public string $routeName = 'verification.send';

    public function test_send_verification_email_endpoint_requires_being_logged_in(): void
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_verification_email_is_sent(): void
    {
        $this->seed();

        $user = User::find(1);

        /** @var \Illuminate\Mail\Transport\ArrayTransport */
        $emailTransport = app('mailer')->getSymfonyTransport();

        $this->assertCount(0, $emailTransport->messages(), 'Start with 0 messages sent');

        $response = $this->actingAs($user)->postJson($this->endpoint);

        $response->assertStatus(202);

        $this->assertCount(1, $emailTransport->messages(), 'Now one message is sent');

        $email = $emailTransport->messages()->pop();

        $this->assertEquals($email->getOriginalMessage()->getTo()[0]->getAddress(), $user->email, 'Email was sent to the correct address');
    }

    public function test_email_verification_link_works(): void
    {
        $this->seed();

        $user = User::find(1);

        $this->assertFalse($user->hasVerifiedEmail());

        //Manually generate the verification URL
        $verificationEmail = new VerifyEmail();
        $url = $verificationEmail->toMail($user)->actionUrl;
        $parts = parse_url($url);

        $relativeUrl = "{$parts['path']}?{$parts['query']}";

        $response = $this->get($relativeUrl);

        $response->assertStatus(204);

        //Update the user model
        $user->refresh();

        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function test_email_verification_link_fails_the_second_time(): void
    {
        $this->seed();

        $user = User::find(1);

        $this->assertFalse($user->hasVerifiedEmail());

        //Manually generate the verification URL
        $verificationEmail = new VerifyEmail();
        $url = $verificationEmail->toMail($user)->actionUrl;
        $parts = parse_url($url);

        $relativeUrl = "{$parts['path']}?{$parts['query']}";

        // First request
        $this->get($relativeUrl);
        //Second request
        $response = $this->get($relativeUrl);

        $response->assertStatus(400);
    }

    public function test_invalid_email_verification_link_fails(): void
    {
        $params = ['id' => 1, 'hash' => 'abcdef'];
        $relativeUrl = route('verification.verify', $params, false);
        $signedUrl = URL::temporarySignedRoute('verification.verify', 10, $params);

        $response = $this->get($relativeUrl);

        // URL Signing fails
        $response->assertStatus(403);

        $response = $this->get($signedUrl);

        // Invalid URL fails
        $response->assertStatus(422);
    }
}
