<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StripeWebhookRequest;
use App\Services\CheckoutService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Stripe\Checkout\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        $logger = new Logger('stripe-webook');
        $logger->pushHandler(new StreamHandler('php://stderr', Level::Info));

        /** @var Session $session */
        $session = request('event')->data->object;

        try {
            $this->checkoutService->resolveCompletedCheckoutSession($session);
        } catch (ModelNotFoundException $e) { // When there is no cart found
            $logger->info('Stripe webhook failed', ['id' => $session->id, 'exception' => $e]);

            return response()->json(status: 204);
        } catch (HttpException $e) { // abort() exceptions
            $logger->info('Stripe webhook failed', ['id' => $session->id, 'exception' => $e]);

            return response()->json(status: 204);
        }

        return response()->json(status: 200);
    }
}
