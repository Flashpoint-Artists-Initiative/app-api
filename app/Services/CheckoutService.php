<?php

declare(strict_types=1);

namespace App\Services;

use Stripe\Checkout\Session;

class CheckoutService
{
    public function __construct(
        protected StripeService $stripeService,
        protected CartService $cartService,
        protected OrderService $orderService,
    ) {
    }

    public function resolveCompletedCheckoutSession(Session $session): void
    {
        $this->stripeService->assertSessionIsPaid($session);
        $order = $this->orderService->createOrderFromSession($session);

        $this->cartService->resolveCompletedCart($session->id, $order->id);
    }
}
