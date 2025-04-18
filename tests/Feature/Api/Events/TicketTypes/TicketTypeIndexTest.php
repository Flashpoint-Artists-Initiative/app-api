<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Events\TicketTypes;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\Ticketing\TicketType;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class TicketTypeIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.ticket-types.index';

    public array $routeParams = ['event' => 1];

    public Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::has('ticketTypes')->where('active', true)->inRandomOrder()->firstOrFail();
        $this->routeParams = ['event' => $this->event->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function ticket_type_index_call_while_not_logged_in_returns_only_active_untrashed_ticket_types(): void
    {
        $ticketTypeCount = TicketType::active()->event($this->event->id)->withoutTrashed()->count();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($ticketTypeCount, 'data');
    }

    #[Test]
    public function ticket_type_index_call_with_permission_returns_pending_types(): void
    {
        TicketType::factory()->for($this->event)->inactive()->count(3)->create();
        $active_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withoutTrashed()->count();
        $ticket_type_count = TicketType::withoutTrashed()->event($this->event->id)->count();

        $this->assertGreaterThan($active_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('ticketTypes.viewPending');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200)->assertJsonCount($ticket_type_count, 'data');
    }

    #[Test]
    public function ticket_type_index_call_with_permission_returns_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        TicketType::factory()->for($this->event)->trashed()->count(3)->create();
        $existing_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->count();
        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200)->assertJsonCount($ticket_type_count, 'data');
    }

    #[Test]
    public function ticket_type_index_call_without_permission_ignores_trashed_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        TicketType::factory()->for($this->event)->trashed()->count(3)->create();
        $existing_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->count();
        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();

        $this->assertGreaterThan($existing_ticket_type_count, $ticket_type_count);

        // No permission for events.viewDeleted
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        // Matches existing event count, not trashed
        $response->assertStatus(200)->assertJsonCount($existing_ticket_type_count, 'data');
    }

    #[Test]
    public function ticket_type_index_call_with_only_trashed_returns_correct_types(): void
    {
        $this->addEndpointParams(['only_trashed' => true]);

        $ticket_type_count = TicketType::where('active', true)->event($this->event->id)->withTrashed()->count();
        $trashed_ticket_type_count = TicketType::where('active', true)->event($this->event->id)->onlyTrashed()->count();

        $this->assertLessThan($ticket_type_count, $trashed_ticket_type_count);

        $user = User::doesntHave('roles')->firstOrFail();
        $user->givePermissionTo('ticketTypes.viewDeleted');

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200)->assertJsonCount($trashed_ticket_type_count, 'data');
    }

    #[Test]
    public function ticket_type_index_call_as_admin_returns_all_types(): void
    {
        $this->addEndpointParams(['with_trashed' => true]);

        TicketType::factory()->for($this->event)->trashed()->count(3)->create();
        $ticket_type_count = TicketType::where('event_id', $this->event->id)->count();
        $all_ticket_type_count = TicketType::withTrashed()->event($this->event->id)->count();

        $this->assertGreaterThan($ticket_type_count, $all_ticket_type_count);

        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);
        $response->assertStatus(200)->assertJsonCount($all_ticket_type_count, 'data');
    }
}
