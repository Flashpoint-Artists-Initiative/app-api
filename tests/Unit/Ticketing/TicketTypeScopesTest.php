<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Event;
use App\Models\Ticketing\TicketType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTypeScopesTest extends TestCase
{
    use RefreshDatabase;

    public bool $seed = true;

    public function test_active_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::active()->get();

        foreach ($ticketTypes as $type) {
            $this->assertEquals($type->active, 1);
        }
    }

    public function test_on_sale_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::onSale()->get();

        foreach ($ticketTypes as $type) {
            $this->assertLessThanOrEqual(Carbon::now(), $type->sale_start_date);
            $this->assertGreaterThanOrEqual(Carbon::now(), $type->sale_end_date);
        }
    }

    public function test_has_quantity_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::hasQuantity()->get();

        foreach ($ticketTypes as $type) {
            $this->assertGreaterThanOrEqual(0, $type->quantity);
        }
    }

    public function test_event_scope_returns_correct_models(): void
    {
        $event = Event::has('ticketTypes')->with('ticketTypes')->first();
        $ticketTypes = TicketType::query()->event($event->id)->get();

        foreach ($ticketTypes as $type) {
            $this->assertEquals($event->id, $type->event_id);
        }
    }
}
