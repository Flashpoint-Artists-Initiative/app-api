<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Event;
use App\Models\Ticketing\TicketType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TicketTypeScopesTest extends TestCase
{
    use LazilyRefreshDatabase;

    public bool $seed = true;

    #[Test]
    public function active_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::active()->get();

        foreach ($ticketTypes as $type) {
            $this->assertEquals($type->active, 1);
        }
    }

    #[Test]
    public function on_sale_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::query()->onSale()->get();

        foreach ($ticketTypes as $type) {
            $this->assertLessThanOrEqual(Carbon::now(), $type->sale_start_date);
            $this->assertGreaterThanOrEqual(Carbon::now(), $type->sale_end_date);
        }
    }

    #[Test]
    public function has_quantity_scope_returns_correct_models(): void
    {
        $ticketTypes = TicketType::hasQuantity()->get();

        foreach ($ticketTypes as $type) {
            $this->assertGreaterThanOrEqual(0, $type->quantity);
        }
    }

    #[Test]
    public function event_scope_returns_correct_models(): void
    {
        $event = Event::has('ticketTypes')->with('ticketTypes')->firstOrFail();
        $ticketTypes = TicketType::query()->event($event->id)->get();

        foreach ($ticketTypes as $type) {
            $this->assertEquals($event->id, $type->event_id);
        }
    }
}
