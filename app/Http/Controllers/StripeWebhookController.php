<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StripeWebhookRequest;
use App\Services\CheckoutService;
use Stripe\Checkout\Session;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService,
    ) {

    }

    public function webhookAction(StripeWebhookRequest $request)
    {
        return match ($request->type) {
            'checkout.session.completed' => $this->checkoutSessionCompleted(),
            default => response()->json(status: 204),
        };
    }

    public function checkoutSessionCompleted()
    {
        /** @var Session $session */
        $session = request('event')->data->object;

        $this->checkoutService->resolveCompletedCheckoutSession($session);

        return response()->json(status: 204);
    }
}
