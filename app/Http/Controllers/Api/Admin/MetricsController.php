<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MetricsRequest;
use App\Services\MetricsService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected MetricsService $metricsService
    ) {
    }

    public function salesDataAction(MetricsRequest $request): JsonResponse
    {
        $this->authorize('metrics.salesData');

        $eventId = (int) $request->event_id;

        return response()->json([
            'data' => $this->orderService->getSalesData($eventId),
        ]);
    }

    public function ticketDataAction(MetricsRequest $request): JsonResponse
    {
        $this->authorize('metrics.ticketData');

        $eventId = (int) $request->event_id;

        return response()->json([
            'data' => $this->metricsService->getTicketQuantityData($eventId),
        ]);
    }
}
