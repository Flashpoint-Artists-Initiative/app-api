<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

trait EnumToArray
{
    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_combine(array_column(self::cases(), 'value'), array_map(fn ($case) => $case->getLabel(), self::cases()));
    }
}
