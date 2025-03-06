<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Events\Waivers;

use App\Models\Event;
use App\Models\Ticketing\Waiver;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class WaiverShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.show';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    public Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->event = Event::has('waivers')->firstOrFail();
        $waiver = $this->event->waivers->firstOrFail();

        $this->addEndpointParams(['event' => $this->event->id, 'waiver' => $waiver->id]);
    }

    #[Test]
    public function waiver_show_call_while_not_logged_in_returns_success(): void
    {
        $waiver = Waiver::where('event_id', $this->event->id)->firstOrFail();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $waiver->id);
    }
}
