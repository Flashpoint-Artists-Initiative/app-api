<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Events\Waivers;

use App\Enums\RolesEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class WaiverCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.store';

    public array $routeParams = ['event' => 1];

    #[Test]
    public function waiver_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => 'test content',
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Minor Waiver',
            'content' => 'test minor content',
            'minor_waiver' => true,
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function waiver_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad title
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => ['array'],
            'content' => 'test content',
        ]);

        $response->assertStatus(422);

        // Bad content
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => ['test content'],
        ]);

        $response->assertStatus(422);

        // Missing title
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => null,
            'content' => 'test content',
        ]);

        $response->assertStatus(422);

        // Missing content
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => null,
        ]);

        $response->assertStatus(422);

        // Bad minor_waiver
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => 'test content',
            'minor_waiver' => 22,
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function waiver_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('waivers.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => 'test content',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function waiver_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'title' => 'Test Waiver',
            'content' => 'test content',
        ]);

        $response->assertStatus(401);
    }
}
