<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Helpers\SvgWithLogoOptions;
use chillerlan\QRCode\QRCode;

class QRCodeService
{
    public function buildQrCode(string $content): ?string
    {
        $options = new SvgWithLogoOptions();

        $out = (new QRCode($options))->render($content);

        return $out;
    }

    public function buildTicketContent(int $userId, int $eventId): string
    {
        $content = ['user_id' => $userId, 'event_id' => $eventId];

        /**
         * It's impossible for json_encode to fail with the parameter types, a TypeError would be thrown instead
         *
         * @var string $json
         */
        $json = json_encode($content);

        return $json;
    }
}
