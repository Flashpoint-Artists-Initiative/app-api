<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutCompleteRequest;
use App\Http\Requests\Checkout\CheckoutCreateRequest;
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
    ) {}

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
        $hasSignedWaivers = $user->hasSignedWaiverForEvent($eventId);

        return response()->json(['data' => [
            'clientSecret' => $session->client_secret,
            'hasSignedWaiver' => $hasSignedWaivers,
        ]]);
    }

    public function createAction(CheckoutCreateRequest $request): JsonResponse
    {
        $this->cartService->expireAllUnexpiredCarts();
        $cart = $this->cartService->createCartAndItems($request->input('tickets', []), $request->input('reserved', []));
        $session = $this->stripeService->createCheckoutFromCart($cart);
        $cart->setStripeCheckoutIdAndSave($session->id);

        /** @var User $user */
        $user = auth()->user();
        $hasSignedWaivers = $user->hasSignedWaiverForEvent($request->event_id);

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

    /**
     * @phpstan-assert Cart $cart
     * @phpstan-assert string $cart->stripe_checkout_id
     */
    protected function validateCart(?Cart $cart): void
    {
        if (is_null($cart) || is_null($cart->stripe_checkout_id)) {
            abort_if($this->cartService->cartWasExpired(), 404, 'Your checkout session expired');
            abort(404, 'No checkout session found');
        }
    }
}
