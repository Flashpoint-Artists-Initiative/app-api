<?php

declare(strict_types=1);

namespace App\Enums;

use Spatie\Permission\Models\Role;

enum RolesEnum: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super-admin';
    case EventManager = 'event-manager';
    case BoxOffice = 'box-office';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
            self::EventManager => 'Event Manager',
            self::BoxOffice => 'Box Office',
        };
    }

    public function model(): Role
    {
        return Role::firstWhere('name', $this->value);
    }

    public static function fromId(int $id): static
    {
        $role = Role::find($id);

        return self::from($role->name);
    }

    public static function fromModel(Role $role): static
    {
        return self::from($role->name);
    }
}
