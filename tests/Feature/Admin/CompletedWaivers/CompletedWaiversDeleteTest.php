<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\CompletedWaivers;

use App\Enums\RolesEnum;
use App\Models\User;
use Tests\ApiRouteTestCase;

class CompletedWaiversDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.completed-waivers.destroy';

    public array $routeParams = ['completed_waiver' => 1];

    public function test_completed_waivers_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_completed_waivers_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->first();

        $this->assertFalse($user->can('completedWaivers.delete'));

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_completed_waivers_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->first();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }
}
