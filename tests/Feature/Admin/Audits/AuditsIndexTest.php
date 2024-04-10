<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Audits;

use App\Enums\RolesEnum;
use App\Models\User;
use OwenIt\Auditing\Models\Audit;
use Tests\ApiRouteTestCase;

class AuditsIndexTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.admin.audits.index';

    public function test_audits_index_call_not_logged_in_returns_error(): void
    {
        $response = $this->get($this->endpoint);

        $response->assertStatus(401);
    }

    public function test_audits_index_call_without_permission_returns_error(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $this->assertFalse($user->can('audits.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(403);
    }

    public function test_audits_index_call_with_permission_returns_success(): void
    {
        $count = Audit::count();
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $this->assertTrue($user->can('audits.viewAny'));

        $response = $this->actingAs($user)->get($this->endpoint);

        $response->assertStatus(200)->assertJsonPath('meta.total', $count);
    }
}
