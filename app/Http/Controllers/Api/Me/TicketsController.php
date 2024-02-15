<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Requests\QrRequest;
use App\Models\User;
use App\Services\QRCodeService;

class TicketsController extends Controller
{
    public function __construct(protected QRCodeService $qrCodeService)
    {
    }

    public function qrAction(QrRequest $request, ?int $eventId = null)
    {
        /** @var User $user */
        $user = auth()->user();
        $ticket = $user->getValidTicketForEvent($eventId);

        if (! $ticket) {
            return response()->json(['error' => 'No valid ticket'], 404);
        }

        $content = $this->qrCodeService->buildTicketContent($user->id, $ticket->ticketType->event_id);

        $qr = $this->qrCodeService->buildQrCode($content);

        return response($qr, headers: ['Content-type' => 'image/svg+xml']);
    }
}
