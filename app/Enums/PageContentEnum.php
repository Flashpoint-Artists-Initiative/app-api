<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageContentEnum: string implements HasLabel
{
    use Concerns\EnumToArray;

    case AppDashboard = 'app-dashboard';

    public function getLabel(): string
    {
        return match ($this) {
            self::AppDashboard => 'App Dashboard',
        };
    }
}
