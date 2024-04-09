<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Helpers\SvgWithLogoOptions;
use chillerlan\QRCode\QRCode;
use Illuminate\Support\Facades\Log;

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

        $json = json_encode($content);

        if ($json === false) {
            Log::channel('stderr')->info('QR code generation failed', ['user_id' => $userId, 'event_id' => $eventId, 'exception' => json_last_error_msg()]);
            abort(500, 'Error Generating QR Code');
        }

        return $json;
    }
}
