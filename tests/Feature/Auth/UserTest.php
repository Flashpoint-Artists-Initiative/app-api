<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\ApiRouteTestCase;

class UserTest extends ApiRouteTestCase
{
    public string $routeName = 'auth.user';

    public function test_auth_user_call_requires_being_logged_in(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_auth_user_call_requires_verified_email(): void
    {
        $this->seed();

        $user = User::where('email_verified_at', null)->first();

        $this->assertFalse($user->hasVerifiedEmail());

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

        $user = User::with('roles')->find(1);
        $resource = new UserResource($user);
        $userJson = json_decode($resource->toJson(), true);

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json->has('data', fn (AssertableJson $json) => $json->whereAll($userJson)));
    }
}
