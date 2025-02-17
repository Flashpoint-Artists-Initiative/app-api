<?php

declare(strict_types=1);

namespace App\Enums;

enum GrantFundingStatus
{
    case Unfunded;
    case MinReached;
    case MaxReached;

    /**
     * @codeCoverageIgnore
     */
    public function label(): string
    {
        return match ($this) {
            self::Unfunded => 'Unfunded',
            self::MinReached => 'Minimum Funding Reached',
            self::MaxReached => 'Maximum Funding Reached',
        };
    }
}
