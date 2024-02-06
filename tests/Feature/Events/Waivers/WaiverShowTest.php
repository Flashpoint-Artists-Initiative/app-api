<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Models\Ticketing\Waiver;
use Database\Seeders\Testing\WaiverSeeder;
use Tests\ApiRouteTestCase;

class WaiverShowTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $seeder = WaiverSeeder::class;

    public string $routeName = 'api.events.waivers.show';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    public function test_waiver_show_call_while_not_logged_in_returns_success(): void
    {
        $waiver = Waiver::where('event_id', 1)->first();

        $response = $this->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('data.id', $waiver->id);
    }
}
