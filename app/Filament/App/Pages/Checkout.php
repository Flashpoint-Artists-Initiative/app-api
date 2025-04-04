<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Models\Ticketing\Cart;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StripeService;
use Filament\Pages\Page;

class Checkout extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.app.pages.checkout';

    public ?string $checkoutSecret;

    public ?string $checkoutId;

    public ?Cart $cart;

    public bool $checkoutComplete = false;

    public function mount(CartService $cartService, StripeService $stripeService): void
    {
        $this->cart = $cartService->getActiveCart();

        if (! $this->cart) {
            $this->redirect(PurchaseTickets::getUrl());

            return;
        }

        $session = $stripeService->getCheckoutSession($this->cart->stripe_checkout_id);

        if ($session->status !== 'open') {
            $this->redirect(PurchaseTickets::getUrl());
        }

        $this->checkoutId = $this->cart->stripe_checkout_id;
        $this->checkoutSecret = $session->client_secret;
    }

    public function completeCheckout(string $sessionId, StripeService $stripeService, CheckoutService $checkoutService): void
    {
        $this->checkoutComplete = true;
        $session = $stripeService->getCheckoutSession($sessionId);
        $checkoutService->resolveCompletedCheckoutSession($session);
    }
}
