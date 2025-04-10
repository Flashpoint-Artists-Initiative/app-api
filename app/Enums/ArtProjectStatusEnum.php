<?php

declare(strict_types=1);

namespace App\Enums;

enum ArtProjectStatusEnum: string
{
    use Concerns\EnumToArray;
    
    case PendingReview = 'pending-review';
    case PendingArtist = 'pending-artist';
    case Approved = 'approved';
    case Denied = 'denied';

    /**
     * @codeCoverageIgnore
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Denied => 'Denied',
            self::PendingArtist => 'Pending Artist Response',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingReview => 'info',
            self::Approved => 'success',
            self::Denied => 'danger',
            self::PendingArtist => 'warning',
        };
    }
}
