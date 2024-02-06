<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use Database\Seeders\Testing\UserWithTicketsSeeder;
use Tests\ApiRouteTestCase;

class MeTicketsTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = UserWithTicketsSeeder::class;

    public string $routeName = 'api.me.tickets';

    public function test_me_tickets_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_me_tickets_call_as_user_returns_success(): void
    {
        // Purchased Tickets
        $user = User::has('purchasedTickets')->first();
        $purchasedTicketsCount = $user->purchasedTickets->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($purchasedTicketsCount, $response->baseResponse->original['data']['purchasedTickets']->count());

        // Reserved Tickets
        $user = User::has('reservedTickets')->first();
        $reservedTicketsCount = $user->reservedTickets->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($reservedTicketsCount, $response->baseResponse->original['data']['reservedTickets']->count());
    }
}
