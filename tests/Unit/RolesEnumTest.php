<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\RolesEnum;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesEnumTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_roles_enum_has_case_for_every_role_in_database(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {
            $this->assertNotEmpty(RolesEnum::from($role->name));
        }
    }

    public function test_database_has_role_for_every_roles_enum_case(): void
    {
        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $this->assertNotEmpty($case->model());
        }
    }

    public function test_roles_enum_has_label_for_every_case(): void
    {
        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $this->assertNotEmpty($case->label());
        }
    }

    public function test_get_roles_enum_from_id_matches_model(): void
    {
        $cases = RolesEnum::cases();

        foreach ($cases as $case) {
            $model = $case->model();
            $enum = RolesEnum::fromId($model->id);
            $this->assertEquals($case, $enum);
        }
    }

    public function test_get_roles_enum_from_model_matches_name(): void
    {
        $role = Role::first();
        $case = RolesEnum::fromModel($role);

        $this->assertEquals($case->value, $role->name);
    }
}
