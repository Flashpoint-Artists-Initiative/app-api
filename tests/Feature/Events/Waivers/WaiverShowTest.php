<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Models\Event;
use App\Models\Ticketing\Waiver;
use Tests\ApiRouteTestCase;

class WaiverShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.show';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    public Event $event;

    public function setUp(): void
    {
        parent::setUp();
        $this->event = Event::has('waivers')->first();
        $waiver = $this->event->waivers->first();

        $this->addEndpointParams(['event' => $this->event->id, 'waiver' => $waiver->id]);
    }

    public function test_waiver_show_call_while_not_logged_in_returns_success(): void
    {
        $waiver = Waiver::where('event_id', $this->event->id)->first();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $waiver->id);
    }
}
