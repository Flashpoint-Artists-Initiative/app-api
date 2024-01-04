<?php

declare(strict_types=1);

namespace Tests\Feature\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Tests\ApiRouteTestCase;

class TicketTypeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.index';

    public array $routeParams = ['event' => 1];

    public function test_ticket_type_index_call_while_not_logged_in_returns_only_active_untrashed_ticket_types(): void
    {
        $ticketTypeCount = TicketType::active()->event(1)->withoutTrashed()->count();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200);
        $this->assertEquals($ticketTypeCount, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_permission_returns_pending_events(): void
    {
        $active_ticket_type_count = TicketType::where('active', true)->event(1)->withoutTrashed()->count();
        $ticket_type_count = TicketType::withoutTrashed()->event(1)->count();

        $this->assertGreaterThan($active_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewPending');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_permission_returns_trashed_events(): void
    {
        $this->buildEndpoint(params: ['event' => 1, 'with_trashed' => true]);

        $existing_ticket_type_count = TicketType::where('active', true)->event(1)->count();
        $ticket_type_count = TicketType::where('active', true)->event(1)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_without_permission_ignores_trashed_events(): void
    {
        $this->buildEndpoint(params: ['event' => 1, 'with_trashed' => true]);

        $existing_ticket_type_count = TicketType::where('active', true)->event(1)->count();
        $ticket_type_count = TicketType::where('active', true)->event(1)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        // No permission for events.viewDeleted
        $user = User::doesntHave('roles')->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        // Matches existing event count, not trashed
        $this->assertEquals($existing_ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_with_only_trashed_returns_correct_events(): void
    {
        $this->buildEndpoint(params: ['event' => 1, 'only_trashed' => true]);

        $ticket_type_count = TicketType::where('active', true)->event(1)->withTrashed()->count();
        $trashed_ticket_type_count = TicketType::where('active', true)->event(1)->onlyTrashed()->count();

        $this->assertLessThan($ticket_type_count, $trashed_ticket_type_count);

        $user = User::doesntHave('roles')->first();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($trashed_ticket_type_count, $response->baseResponse->original->count());
    }

    public function test_ticket_type_index_call_as_admin_returns_all_events(): void
    {
        $this->buildEndpoint(params: ['event' => 1, 'with_trashed' => true]);

        $ticket_type_count = TicketType::where('event_id', 1)->count();
        $all_ticket_type_count = TicketType::withTrashed()->event(1)->count();

        $this->assertGreaterThan($ticket_type_count, $all_ticket_type_count);

        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200);
        $this->assertEquals($all_ticket_type_count, $response->baseResponse->original->count());
    }
}
