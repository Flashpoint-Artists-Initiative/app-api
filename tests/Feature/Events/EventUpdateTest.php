<?php

declare(strict_types=1);

namespace Tests\Feature\Events;

use App\Enums\RolesEnum;
use App\Models\User;
use Carbon\Carbon;
use Tests\ApiRouteTestCase;

class EventUpdateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.update';

    public array $routeParams = ['event' => 1];

    public function test_event_update_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => new Carbon('-1 day'),
            'end_date' => new Carbon('+1 week'),
            'contact_email' => fake()->safeEmail(),
        ]);

        $response->assertStatus(200);
    }

    public function test_event_update_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        // Bad email
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => new Carbon('-1 day'),
            'end_date' => new Carbon('+1 week'),
            'contact_email' => 'bad_email',
        ]);

        $response->assertStatus(422);

        //Bad start_date
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => 'bad_date',
            'end_date' => new Carbon('+1 week'),
            'contact_email' => 'test@example.com',
        ]);

        $response->assertStatus(422);

        //Bad end_date
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => new Carbon('-1 week'),
            'end_date' => 'bad_date',
            'contact_email' => 'test@example.com',
        ]);

        $response->assertStatus(422);

        //Bad name
        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => null,
            'start_date' => 'bad_date',
            'end_date' => new Carbon('+1 week'),
            'contact_email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_event_update_call_without_permission_returns_error(): void
    {

        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('events.update'));

        $response = $this->actingAs($user)->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => new Carbon('-1 day'),
            'end_date' => new Carbon('+1 week'),
            'contact_email' => fake()->safeEmail(),
        ]);

        $response->assertStatus(403);
    }

    public function test_event_update_call_not_logged_in_returns_error(): void
    {
        $response = $this->patchJson($this->endpoint, [
            'name' => 'Test Event',
            'start_date' => new Carbon('-1 day'),
            'end_date' => new Carbon('+1 week'),
            'contact_email' => fake()->safeEmail(),
        ]);

        $response->assertStatus(401);
    }
}
