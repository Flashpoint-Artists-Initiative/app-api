<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Lockdown;

use App\Enums\RolesEnum;
use App\Models\User;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class LockdownEnableTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.lockdown.enable';

    public array $routeParams = ['type' => 'ticket'];

    #[Test]
    public function lockdown_enable_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->post($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function lockdown_enable_call_without_permission_in_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function lockdown_enable_call_with_permission_in_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(204);
    }

    #[Test]
    public function lockdown_enable_call_disables_ticket_endpoint(): void
    {
        $ticketEndpoint = route('api.ticket-types.reserved-tickets.store', ['ticket_type' => 1]);
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(204);

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(423);
    }

    #[Test]
    public function lockdown_enable_site_call_disables_ticket_endpoint(): void
    {
        $this->addEndpointParams(['type' => 'site']);

        $ticketEndpoint = route('api.ticket-types.reserved-tickets.store', ['ticket_type' => 1]);
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(204);

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'email' => 'notauser@example.com',
            'expiration_date' => new Carbon('+1 week'),
        ]);

        $response->assertStatus(423);
    }

    #[Test]
    public function lockdown_enable_call_disables_volunteer_endpoint(): void
    {
        $this->addEndpointParams(['type' => 'volunteer']);

        $ticketEndpoint = route('api.events.teams.store', ['event' => 1]);
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(204);

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(423);
    }

    #[Test]
    public function lockdown_enable_site_call_disables_volunteer_endpoint(): void
    {
        $this->addEndpointParams(['type' => 'site']);

        $ticketEndpoint = route('api.events.teams.store', ['event' => 1]);
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(201);

        $response = $this->actingAs($user)->post($this->endpoint);

        $response->assertStatus(204);

        $response = $this->actingAs($user)->postJson($ticketEndpoint, [
            'name' => 'Test Team',
            'description' => fake()->paragraph(),
            'email' => fake()->safeEmail(),
            'active' => true,
        ]);

        $response->assertStatus(423);
    }
}
