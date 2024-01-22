<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookMiddleware
{
    /**
     * Verify that the request is coming from the stripe API
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        $event = Webhook::constructEvent($request->getContent(), $signature, $secret);
        $request->merge(['event' => $event]);

        return $next($request);
    }
}
