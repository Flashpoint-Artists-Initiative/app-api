<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Lockdown;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class LockdownDisableTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.lockdown.disable';

    public array $routeParams = ['type' => 'ticket'];

    public function test_lockdown_disable_call_while_not_logged_in_returns_error(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_lockdown_disable_call_without_permission_in_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_lockdown_disable_call_with_permission_in_returns_success(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(204);
    }
}
