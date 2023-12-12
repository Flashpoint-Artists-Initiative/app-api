<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public string $endpoint;

    public function setUp(): void
    {
        parent::setUp();
        $this->endpoint = route('logout', [], false);
    }

    public function test_logging_out_invalidates_auth_token(): void
    {
        $user = User::find(1);

        // Set user JWT token
        auth()->login($user);

        $this->assertEquals(auth()->user(), $user);

        $response = $this->post($this->endpoint);

        $response->assertStatus(200);

        $this->assertEmpty(auth()->user());
    }

    public function test_logging_out_requires_being_logged_in(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }
}
