<?php

declare(strict_types=1);

namespace App\Stripe;

use App\Models\Ticketing\Cart;
use Stripe\Checkout\Session;
use Stripe\StripeClient;

/**
 * A Wrapper for StripeClient with a few helper methods
 *
 * @mixin StripeClient
 */
class StripeGateway
{
    public function __construct(public StripeClient $stripeClient)
    {
    }

    // These magic methods redirect all calls to the $stripeClient
    public function __get($name)
    {
        return $this->stripeClient->$name;
    }

    public function __call($name, $arguments)
    {
        return $this->stripeClient->$name($arguments);
    }

    public function createCheckoutFromCart(Cart $cart): Session
    {
        $client_reference_id = sprintf('Event: %s - User: %d - Cart: %d', $cart->event->name, $cart->user_id, $cart->id);

        $checkout_session = $this->stripeClient->checkout->sessions->create([
            'return_url' => config('services.stripe.return_url'),
            'mode' => 'payment',
            'ui_mode' => 'embedded',
            'customer_email' => auth()->user()->email,
            'client_reference_id' => $client_reference_id,
            'customer_creation' => 'if_required',
            'line_items' => $this->buildLineItems($cart),
            'metadata' => [
                'event_id' => $cart->event->id,
                'user_id' => $cart->user_id,
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
        $this->stripeClient->checkout->sessions->expire($cart->stripe_checkout_id);
    }

    protected function buildLineItems(Cart $cart): array
    {
        $array = [];

        foreach ($cart->items as $item) {
            $array[] = [
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
                'tax_rates' => [config('services.stripe.tax_rate')],
            ];
        }

        return $array;
    }
}
