<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Helpers\SVGWithLogoOptions;
use chillerlan\QRCode\QRCode;

class QRCodeService
{
    public function buildQrCode(string $content): ?string
    {
        $options = new SVGWithLogoOptions();

        $out = (new QRCode($options))->render($content);

        return $out;
    }

    public function buildTicketContent(int $userId, int $eventId): string
    {
        $content = ['user_id' => $userId, 'event_id' => $eventId];

        return json_encode($content);
    }
}
