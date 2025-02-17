<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Lockdown;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class LockdownStatusTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.lockdown.status';

    #[Test]
    public function lockdown_status_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function lockdown_status_call_without_permission_in_returns_success(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200);
    }
}
