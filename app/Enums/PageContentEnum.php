<?php

declare(strict_types=1);

namespace App\Enums;

enum PageContentEnum: string
{
    use Concerns\EnumToArray;

    case AppDashboard = 'app-dashboard';

    public function label(): string
    {
        return match ($this) {
            self::AppDashboard => 'App Dashboard',
        };
    }
}
