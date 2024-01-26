<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use Database\Seeders\Testing\EventWithMultipleTicketTypesSeeder;
use Tests\ApiRouteTestCase;

class TicketTypeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = EventWithMultipleTicketTypesSeeder::class;

    public string $routeName = 'api.events.ticket-types.index';

    public array $routeParams = ['event' => 1];

    public Event $event;

    public function setUp(): void
    {
        parent::setUp();
        $this->event = Event::has('ticketTypes')->where('active', true)->inRandomOrder()->first();
        $this->routeParams = ['event' => $this->event->id];
        $this->buildEndpoint();
    }

    public function test_ticket_type_index_call_while_not_logged_in_returns_only_active_untrashed_ticket_types(): void
    {
        $ticketTypeCount = TicketType::active()->event($this->event->id)->withoutTrashed()->count();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($ticketTypeCount, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_permission_returns_pending_types(): void
    {
        $active_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withoutTrashed()->count();
        $ticket_type_count = TicketType::withoutTrashed()->event($this->event->id)->count();

        $this->assertGreaterThan($active_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewPending');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_permission_returns_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $existing_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->count();
        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_without_permission_ignores_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $existing_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->count();
        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        // No permission for events.viewDeleted
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        // Matches existing event count, not trashed
        $this->assertEquals($existing_ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_only_trashed_returns_correct_types(): void
    {
        $this->addEndpointParams(['only_trashed' => true]);

        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();
        $trashed_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->onlyTrashed()->count();

        $this->assertLessThan($ticket_type_count, $trashed_ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($trashed_ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_as_admin_returns_all_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        $ticket_type_count = TicketType::where('event_id', $this->event->id)->count();
        $all_ticket_type_count = TicketType::withTrashed()->event($this->event->id)->count();

        $this->assertGreaterThan($ticket_type_count, $all_ticket_type_count);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($all_ticket_type_count, $response->baseResponse->original->count());
    }
}
