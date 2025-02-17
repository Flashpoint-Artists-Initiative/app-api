<?php

declare(strict_types=1);

namespace App\Enums;

enum ArtProjectStatus: string
{
    case PendingReview = 'pending-review';
    case Approved = 'approved';
    case Denied = 'denied';
    case PendingArtist = 'pending-artist';

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
}
