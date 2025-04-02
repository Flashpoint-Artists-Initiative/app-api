<?php

declare(strict_types=1);

namespace Tests\Unit\Ticketing;

use App\Models\Ticketing\Cart;
use App\Services\StripeService;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Service\Checkout\CheckoutServiceFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class StripeServiceTest extends TestCase
{
    public StripeService $stripeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stripeService = app()->make(StripeService::class);
    }

    #[Test]
    public function get_invalid_checkout_session(): void
    {
        $this->expectException(HttpException::class);
        $this->stripeService->getCheckoutSession('bad_id');
    }

    #[Test]
    public function get_magic_method(): void
    {
        $checkoutObj = $this->stripeService->checkout;
        $this->assertInstanceOf(CheckoutServiceFactory::class, $checkoutObj);
    }

    #[Test]
    public function call_magic_method(): void
    {
        $key = $this->stripeService->getApiKey();
        $this->assertEquals(config('services.stripe.secret'), $key);
    }

    #[Test]
    public function expired_expired_cart_session(): void
    {
        $session = $this->stripeService->checkout->sessions->all(['status' => 'expired', 'limit' => 1])->first();

        $this->assertNotNull($session);

        $cart = new Cart;
        $cart->stripe_checkout_id = $session->id;

        $this->assertNotEmpty($cart->stripe_checkout_id);
        $this->stripeService->expireCheckoutFromCart($cart);

        // If we got here, the test passed
    }
}
