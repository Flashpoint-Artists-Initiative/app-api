<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\User;
use Carbon\Carbon;
use Tests\ApiRouteTestCase;

class ReservedTicketCreateTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.ticket-types.reserved-tickets.store';

    public array $routeParams = ['ticket_type' => 1];

    public function test_reserved_ticket_create_call_with_valid_data_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $this->assertNull(User::where('email', 'notauser@example.com')->first());

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(201);
    }

    public function test_reserved_ticket_create_call_with_matching_email_returns_a_successful_response(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => $user->email,
        ]);

        $response->assertStatus(201)->assertJsonPath('data.user_id', $user->id)->assertJsonPath('data.email', null);
    }

    public function test_reserved_ticket_create_call_with_invalid_data_returns_a_validation_error(): void
    {
        $user = User::role(RolesEnum::Admin)->first();
        $this->assertNull(User::where('email', 'notauser@example.com')->first());

        // Bad email
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'not_a_email',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(422);

        //Bad expiration_date
        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => 'bad date',
        ]);

        $response->assertStatus(422);
    }

    public function test_reserved_ticket_create_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('reservedTickets.create'));

        $response = $this->actingAs($user)->postJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(403);
    }

    public function test_reserved_ticket_create_call_not_logged_in_returns_error(): void
    {
        $response = $this->postJson($this->endpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(401);
    }
}
