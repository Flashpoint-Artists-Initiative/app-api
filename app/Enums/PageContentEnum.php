<?php

declare(strict_types=1);

namespace App\Enums;

enum PageContentEnum: string
{
    case AppDashboard = 'app-dashboard';

    public function label(): string
    {
        return match ($this) {
            self::AppDashboard => 'App Dashboard',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_combine(array_column(self::cases(), 'value'), array_map(fn ($case) => $case->label(), self::cases()));
    }
}
