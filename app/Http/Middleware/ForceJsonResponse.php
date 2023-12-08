<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Routing\ResponseFactory;

class ForceJsonResponse
{
    public function __construct(public ResponseFactory $factory)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        // First, set the header so any other middleware knows we're
        // dealing with a should-be JSON response. 
        $request->headers->set('Accept', 'application/json');

        // Get the response
        $response = $next($request);

        // If the response is not strictly a JsonResponse, we make it
        if (!$response instanceof JsonResponse) {
            $response = $this->factory->json(
                $response->content(),
                $response->status(),
                $response->headers->all()
            );
        }
        
        return $response;
    }
}