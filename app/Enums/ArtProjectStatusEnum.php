<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ArtProjectStatusEnum: string implements HasLabel, HasColor
{
    use Concerns\EnumToArray;

    case PendingReview = 'pending-review';
    case PendingArtist = 'pending-artist';
    case Approved = 'approved';
    case Denied = 'denied';

    /**
     * @codeCoverageIgnore
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Denied => 'Denied',
            self::PendingArtist => 'Pending Artist Response',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PendingReview => 'info',
            self::Approved => 'success',
            self::Denied => 'danger',
            self::PendingArtist => 'warning',
        };
    }
}
