<?php

declare(strict_types=1);

namespace Tests\Feature\Me;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class MeWaiversTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.me.waivers';

    #[Test]
    public function me_waivers_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function me_waivers_call_as_user_returns_success(): void
    {
        $user = User::has('waivers')->firstOrFail();
        $waiverCount = $user->waivers->count();
        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonCount($waiverCount, 'data');
    }
}
