<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class LogoutTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'logout';

    #[Test]
    public function logging_out_invalidates_auth_token(): void
    {
        $user = User::findOrFail(1);

        // Set user JWT token
        auth()->login($user);

        $this->assertEquals(auth()->user(), $user);

        $response = $this->post($this->endpoint);

        $response->assertStatus(200);

        $this->assertEmpty(auth()->user());
    }

    #[Test]
    public function logging_out_requires_being_logged_in(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }
}
