<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Volunteering\Shifts;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Models\Volunteering\ShiftType;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiRouteTestCase;

class ShiftDeleteTest extends ApiRouteTestCase
{
    public bool $seed = true;

    public string $routeName = 'api.shift-types.shifts.destroy';

    public array $routeParams = ['shift_type' => 1, 'shift' => 1];

    protected function setUp(): void
    {
        parent::setUp();
        $shiftType = ShiftType::has('shifts')->firstOrFail();
        $shift = $shiftType->shifts()->firstOrFail();
        $this->routeParams = ['shift_type' => $shiftType->id, 'shift' => $shift->id];
        $this->buildEndpoint();
    }

    #[Test]
    public function shift_delete_call_while_not_logged_in_fails(): void
    {
        $response = $this->delete($this->endpoint);

        $response->assertStatus(401);
    }

    #[Test]
    public function shift_delete_call_without_permission_fails(): void
    {
        $user = User::doesntHave('roles')->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(403);
    }

    #[Test]
    public function shift_delete_call_as_admin_succeeds(): void
    {
        $user = User::role(RolesEnum::Admin)->firstOrFail();

        $response = $this->actingAs($user)->delete($this->endpoint);

        $response->assertStatus(200);
    }
}
