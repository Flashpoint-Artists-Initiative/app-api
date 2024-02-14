<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutCompleteRequest;
use App\Http\Requests\Checkout\CheckoutCreateGeneralSaleRequest;
use App\Http\Requests\Checkout\CheckoutCreateReservedRequest;
use App\Models\Ticketing\Cart;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    protected static bool $cartWasDeleted = false;

    public function __construct(
        protected StripeService $stripeService,
        protected CartService $cartService,
        protected CheckoutService $checkoutService,
    ) {

    }

    /**
     * Return a user's cart
     */
    public function indexAction(): JsonResponse
    {
        $cart = $this->cartService->getActiveCart();
        $this->validateCart($cart);

        $session = $this->stripeService->getCheckoutSession($cart->stripe_checkout_id);

        $eventId = $this->cartService->getEventIdFromCart($cart);

        /** @var User $user */
        $user = auth()->user();
        $hasSignedWaivers = $user->hasSignedWaiverFromEvent($eventId);

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
            'hasSignedWaiver' => $hasSignedWaivers,
        ]]);
    }

    public function createGeneralSaleAction(CheckoutCreateGeneralSaleRequest $request): JsonResponse
    {
        $this->cartService->expireAllUnexpiredCarts();
        $cart = $this->cartService->createGeneralSaleCartAndItems($request->tickets);
        $session = $this->stripeService->createCheckoutFromCart($cart);
        $cart->setStripeCheckoutIdAndSave($session->id);

        /** @var User $user */
        $user = auth()->user();
        $hasSignedWaivers = $user->hasSignedWaiverFromEvent($request->event_id);

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
            'hasSignedWaiver' => $hasSignedWaivers,
        ]], 201);
    }

    public function createReservedAction(CheckoutCreateReservedRequest $request): JsonResponse
    {
        $this->cartService->expireAllUnexpiredCarts();
        $cart = $this->cartService->createReservedCartAndItems($request->tickets);
        $session = $this->stripeService->createCheckoutFromCart($cart);
        $cart->setStripeCheckoutIdAndSave($session->id);

        /** @var User $user */
        $user = auth()->user();
        $hasSignedWaivers = $user->hasSignedWaiverFromEvent($request->event_id);

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
            'hasSignedWaiver' => $hasSignedWaivers,
        ]], 201);
    }

    public function deleteAction(): JsonResponse
    {
        $cart = $this->cartService->getActiveCart();

        if ($cart) {
            $cart->delete();

            return response()->json(status: 204);
        }

        return response()->json(status: 404);
    }

    public function completeCheckoutAction(CheckoutCompleteRequest $request): JsonResponse
    {
        $session = $this->stripeService->getCheckoutSession($request->session_id);
        $this->checkoutService->resolveCompletedCheckoutSession($session);

        return response()->json(status: 204);
    }

    protected function validateCart(?Cart $cart): void
    {
        if (is_null($cart)) {
            abort_if($this->cartService->cartWasExpired(), 404, 'Your checkout session expired');
            abort(404, 'No checkout session found');
        }
    }
}
