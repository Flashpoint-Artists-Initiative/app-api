<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class UserTest extends ApiRouteTestCase
{
    use RefreshDatabase;

    public string $routeName = 'auth.user';

    public function test_auth_user_call_requires_being_logged_in(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_auth_user_call_requires_verified_email(): void
    {
        $this->seed();

        $user = User::find(1);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_auth_user_call_returns_current_user(): void
    {
        User::factory()->create([
            'legal_name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $user = User::find(1);
        $userJson = json_decode($user->toJson(), true);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->whereAll($userJson));
    }
}
