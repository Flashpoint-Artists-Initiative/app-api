<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class UsersCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.users.store';

    public function test_users_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'password' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_users_create_call_with_different_birthday_formats_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date('m/d/Y'),
            'password' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_users_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad legal_name
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => null,
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'password' => 'password',
        ]);

        $response->assertStatus(422);

        // Bad email
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'email' => 'bad_email',
            'birthday' => fake()->date(),
            'password' => 'password',
        ]);

        $response->assertStatus(422);

        //Bad birthday
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'birthday' => 'This is an invalid date',
            'password' => 'password',
        ]);

        $response->assertStatus(422);

        //Bad password
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'password' => 'short',
        ]);

        $response->assertStatus(422);
    }

    public function test_users_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('users.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_users_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'legal_name' => fake()->name(),
            'preferred_name' => fake()->userName(),
            'email' => fake()->safeEmail(),
            'birthday' => fake()->date(),
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }
}
