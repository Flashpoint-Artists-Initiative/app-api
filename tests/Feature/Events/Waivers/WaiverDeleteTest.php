<?php

declare(strict_types=1);

namespace Tests\Feature\Events\Waivers;

use App\Enums\RolesEnum;
use App\Models\Event;
use App\Models\User;
use Tests\ApiRouteTestCase;

class WaiverDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.events.waivers.destroy';

    public array $routeParams = ['event' => 1, 'waiver' => 1];

    public function setUp(): void
    {
        parent::setUp();
        $event = Event::has('waivers')->firstOrFail();
        $waiver = $event->waivers->firstOrFail();

        $this->addEndpointParams(['event' => $event->id, 'waiver' => $waiver->id]);
    }

    public function test_waiver_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_waiver_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_waiver_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }
}
