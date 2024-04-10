<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\User;
use Tests\ApiRouteTestCase;

class WaiverUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.update';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    public function setUp(): void
    {
        parent::setUp();
        $event = Event::has('waivers')->firstOrFail();
        $waiver = $event->waivers->firstOrFail();

        $this->addEndpointParams(['event' => $event->id, 'waiver' => $waiver->id]);
    }

    public function test_waiver_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => 'Test Waiver Update',
            'content' => 'test content update',
        ]);

        $response->assertStatus(200);
    }

    public function test_waiver_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        // Bad title
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => ['data'],
        ]);

        $response->assertStatus(422);

        //Bad content
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'content' => null,
        ]);

        $response->assertStatus(422);

        //Bad minor_waiver
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'minor_waiver' => 'sure',
        ]);

        $response->assertStatus(422);
    }

    public function test_waiver_update_call_without_permission_returns_error(): void
    {

        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('waivers.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => 'Test Waiver Update',
            'content' => 'test content update',
        ]);

        $response->assertStatus(403);
    }

    public function test_waiver_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'title' => 'Test Waiver Update',
            'content' => 'test content update',
        ]);

        $response->assertStatus(401);
    }

    public function test_waiver_update_call_with_completed_waivers_returns_error(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        CompletedWaiver::create(['user_id' => $user->id, 'waiver_id' => 1]);

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'title' => 'Test Waiver Update',
            'content' => 'test content update',
        ]);

        $response->assertStatus(403);
    }
}
