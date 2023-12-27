<?php

declare(strict_types=1);

namespace App\Enums;

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
}
