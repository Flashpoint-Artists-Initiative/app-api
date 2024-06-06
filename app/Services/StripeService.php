<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use Stripe\TaxRate;

/**
 * @mixin StripeClient
 */
class StripeService
{
    const TAX_RATE_CACHE_PREFIX = 'stripe.tax-rate.';

    public function __construct(public StripeClient $stripeClient)
    {
    }

    // These magic methods redirect all calls to the $stripeClient
    public function __get(mixed $name): mixed
    {
        return $this->stripeClient->$name;
    }

    public function __call(mixed $name, mixed $arguments): mixed
    {
        return $this->stripeClient->$name($arguments);
    }

    public function getCheckoutSession(string $id): Session
    {
        try {
            return $this->stripeClient->checkout->sessions->retrieve($id);
        } catch (InvalidRequestException $e) {
            abort(422, 'Invalid Stripe checkout session id');
        }
    }

    public function createCheckoutFromCart(Cart $cart): Session
    {
        $client_reference_id = sprintf('Event: %s - User: %d - Cart: %d', $cart->event->name, $cart->user_id, $cart->id);
        $user = auth()->user();
        abort_unless($user instanceof User, 400, 'User not found');

        $checkout_session = $this->stripeClient->checkout->sessions->create([
            'redirect_on_completion' => 'never',
            'payment_method_configuration' => config('services.stripe.payment_method_configuration'),
            'mode' => 'payment',
            'ui_mode' => 'embedded',
            'customer_email' => $user->email,
            'client_reference_id' => $client_reference_id,
            'customer_creation' => 'if_required',
            'line_items' => $this->buildLineItems($cart),
            'expires_at' => now()->addMinutes(31)->format('U'),
            'metadata' => [
                'event_id' => $cart->event->id,
                'user_id' => $cart->user_id,
                'ticket_quantity' => $cart->quantity,
            ],
            'custom_text' => [
                'submit' => [
                    'message' => "You'll receive an email confirmation with your ticket information.",
                ],
            ],
        ]);

        return $checkout_session;
    }

    public function expireCheckoutFromCart(Cart $cart): void
    {
        if ($cart->stripe_checkout_id) {
            try {
                $this->stripeClient->checkout->sessions->expire($cart->stripe_checkout_id);
            } catch (ApiErrorException $e) {  // Occurs when the session is already expired by Stripe
            }
        }
    }

    /**
     * Create the line_items array for a checkout session from the cart's items
     *
     * @return array<string, mixed>
     */
    protected function buildLineItems(Cart $cart): array
    {
        return $cart->items->map(function (CartItem $item) {
            return [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->ticketType->name,
                        'metadata' => [
                            'ticket_type_id' => $item->ticketType->id,
                        ],
                    ],
                    'unit_amount' => $item->ticketType->price * 100, // Stripe price is in cents
                ],
                'quantity' => $item->quantity,
                'tax_rates' => $this->getTaxRatesArray(),
            ];
        })->toArray();
    }

    /**
     * @return string[]
     */
    public function getTaxRatesArray(): array
    {
        $tax_rates = explode(',', config('services.stripe.tax_rates'));
        $tax_rates = array_map('trim', $tax_rates);
        $tax_rates = array_filter($tax_rates);

        return $tax_rates;
    }

    public function assertSessionIsPaid(Session $session): void
    {
        abort_if($session->status !== 'complete', 422, 'Session has not been completed');
        abort_if($session->payment_status !== 'paid', 422, 'Session payment is not complete');
    }

    /**
     * Pull and cache tax rate data from the Stripe API
     */
    public function getTaxRate(string $taxRateId): TaxRate
    {
        return Cache::remember(
            self::TAX_RATE_CACHE_PREFIX . $taxRateId,
            now()->addDay(),
            function () use ($taxRateId) {
                return $this->stripeClient->taxRates->retrieve($taxRateId);
            });
    }

    /**
     * @return array<string,float> The tax rate description => it's percentage value (0-100)
     */
    public function getTaxRatePercentages(): array
    {
        $rates = $this->getTaxRatesArray();

        $output = [];

        foreach ($rates as $rate) {
            $stripeObj = $this->getTaxRate($rate);
            $output[$stripeObj->description] = $stripeObj->percentage;
        }

        return $output;
    }
}
