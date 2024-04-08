<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LockdownService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LockdownController extends Controller
{
    public function __construct(protected LockdownService $lockdownService)
    {
    }

    public function enableLockdown(Request $request): JsonResponse
    {
        $this->authorize('lockdown.set');

        $this->lockdownService->setLockdown($request->type, true);

        return response()->json(status: 204);
    }

    public function disableLockdown(Request $request): JsonResponse
    {
        $this->authorize('lockdown.set');

        $this->lockdownService->setLockdown($request->type, false);

        return response()->json(status: 204);
    }

    public function getLockdownStatus(): JsonResponse
    {
        $status = $this->lockdownService->getLockdownStatus();

        return response()->json(['data' => $status]);
    }
}
