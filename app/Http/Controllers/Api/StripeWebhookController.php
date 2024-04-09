<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StripeWebhookRequest;
use App\Services\CheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {
    }

    public function webhookAction(StripeWebhookRequest $request): JsonResponse
    {
        return match ($request->type) {
            'checkout.session.completed' => $this->checkoutSessionCompleted(),
            default => response()->json(status: 204),
        };
    }

    public function checkoutSessionCompleted(): JsonResponse
    {
        /** @var Session $session */
        $session = request('event')->data->object;

        try {
            $this->checkoutService->resolveCompletedCheckoutSession($session);
        } catch (ModelNotFoundException $e) { // When there is no cart found
            Log::channel('stderr')->info('Stripe webhook failed', ['id' => $session->id, 'exception' => $e]);

            return response()->json(status: 204);
        } catch (HttpException $e) { // abort() exceptions
            Log::channel('stderr')->info('Stripe webhook failed', ['id' => $session->id, 'exception' => $e]);

            return response()->json(status: 204);
        }

        return response()->json(status: 200);
    }
}
