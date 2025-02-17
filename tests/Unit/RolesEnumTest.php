<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\RolesEnum;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesEnumTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[Test]
    public function roles_enum_has_case_for_every_role_in_database(): void
    {
        $roles = Role::all();

        $this->expectNotToPerformAssertions();

        foreach ($roles as $role) {
            $role = RolesEnum::from($role->name);
        }
    }

    #[Test]
    public function database_has_role_for_every_roles_enum_case(): void
    {
        $this->expectNotToPerformAssertions();

        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $case->model();
        }
    }

    #[Test]
    public function roles_enum_has_label_for_every_case(): void
    {
        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $this->assertNotEmpty($case->label());
        }
    }

    #[Test]
    public function get_roles_enum_from_id_matches_model(): void
    {
        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $model = $case->model();
            $enum = RolesEnum::fromId((int) $model->id);
            $this->assertEquals($case, $enum);
        }
    }

    #[Test]
    public function get_roles_enum_from_model_matches_name(): void
    {
        $role = Role::firstOrFail();
        $case = RolesEnum::fromModel($role);

        $this->assertEquals($case->value, $role->name);
    }
}
