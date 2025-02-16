<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Models\Ticketing\Waiver;
use Tests\ApiRouteTestCase;

class WaiverIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.index';

    public array $routeParams = ['event' => 1];

    public function test_waiver_index_call_while_not_logged_in_returns_success(): void
    {
        $waiverCount = Waiver::where('event_id', 1)->count();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($waiverCount, 'data');
    }
}
