<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\CompletedWaivers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\Waiver;
use App\Models\User;
use Database\Seeders\Testing\WaiverSeeder;
use Tests\ApiRouteTestCase;

class CompletedWaiversCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = WaiverSeeder::class;

    public string $routeName = 'api.admin.completed-waivers.store';

    public function test_completed_waivers_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => Waiver::first()->id,
            'user_id' => User::doesntHave('roles')->first()->id,
            'form_data' => json_encode(['a' => 1, 'b' => 2]),
            'paper_completion' => false,
        ]);

        $response->assertStatus(201);
    }

    public function test_completed_waivers_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $waiverId = Waiver::first()->id;
        $userId = User::doesntHave('roles')->first()->id;
        $formData = json_encode(['a' => 1, 'b' => 2]);

        // Bad waiver_id - not an int
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => 'abc',
            'user_id' => $userId,
            'form_data' => $formData,
            'paper_completion' => false,
        ]);

        $response->assertStatus(422);

        // Bad waiver_id - no matching waiver
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => 999,
            'user_id' => $userId,
            'form_data' => $formData,
            'paper_completion' => false,
        ]);

        $response->assertStatus(422);

        // Bad user_id - not an int
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => $waiverId,
            'user_id' => 'invalid',
            'form_data' => $formData,
            'paper_completion' => false,
        ]);

        $response->assertStatus(422);

        // Bad user_id - no matching user
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => $waiverId,
            'user_id' => 999,
            'form_data' => $formData,
            'paper_completion' => false,
        ]);

        $response->assertStatus(422);

        //Bad form_data
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => $waiverId,
            'user_id' => $userId,
            'form_data' => '{"invalid" json}',
            'paper_completion' => false,
        ]);

        $response->assertStatus(422);

        //Bad paper_completion
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => $waiverId,
            'user_id' => $userId,
            'form_data' => $formData,
            'paper_completion' => 'abc',
        ]);

        $response->assertStatus(422);
    }

    public function test_completed_waivers_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('completedWaivers.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'waiver_id' => Waiver::first()->id,
            'user_id' => User::doesntHave('roles')->first()->id,
            'form_data' => json_encode(['a' => 1, 'b' => 2]),
            'paper_completion' => false,
        ]);

        $response->assertStatus(403);
    }

    public function test_completed_waivers_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'waiver_id' => Waiver::first()->id,
            'user_id' => User::doesntHave('roles')->first()->id,
            'form_data' => json_encode(['a' => 1, 'b' => 2]),
            'paper_completion' => false,
        ]);

        $response->assertStatus(401);
    }
}
