<?php

declare(strict_types=1);

namespace App\Enums;

use Spatie\Permission\Models\Role;

enum RolesEnum: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super-admin';
    case EventManager = 'event-manager';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::SuperAdmin => 'Super Admin',
            self::EventManager => 'Event Manager',
        };
    }

    public function model(): Role
    {
        return Role::firstWhere('name', $this->value);
    }

    public static function fromId($id): static
    {
        $role = Role::find($id);

        return self::from($role->name);
    }
}
