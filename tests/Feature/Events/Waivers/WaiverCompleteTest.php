<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Enums\RolesEnum;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class WaiverCompleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.complete';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    #[Test]
    public function waiver_complete_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'form_data' => '{"test": "123"}',
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function waiver_complete_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad form_data
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'form_data' => '{"bad-data}',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function waiver_complete_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'form_data' => '{"test": "123"}',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function waiver_complete_call_with_completed_waivers_returns_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        CompletedWaiver::create(['user_id' => $user->id, 'waiver_id' => 1]);

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'form_data' => '{"test": "123"}',
        ]);

        $response->assertStatus(403);
    }
}
