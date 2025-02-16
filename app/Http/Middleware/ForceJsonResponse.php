<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function __construct(public ResponseFactory $factory) {}

    public function handle(Request $request, Closure $next): JsonResponse
    {
        return $next($request);
    }
}
