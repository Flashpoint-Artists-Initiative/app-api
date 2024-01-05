<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TicketType;
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
        $ticketTypes = TicketType::query()->event(1)->get();

        foreach ($ticketTypes as $type) {
            $this->assertEquals(1, $type->event_id);
        }
    }
}
