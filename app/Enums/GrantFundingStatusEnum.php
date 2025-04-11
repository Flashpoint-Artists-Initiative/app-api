<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GrantFundingStatusEnum implements HasLabel
{
    use Concerns\EnumToArray;

    case Unfunded;
    case MinReached;
    case MaxReached;

    /**
     * @codeCoverageIgnore
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::Unfunded => 'Unfunded',
            self::MinReached => 'Minimum Funding Reached',
            self::MaxReached => 'Maximum Funding Reached',
        };
    }
}
