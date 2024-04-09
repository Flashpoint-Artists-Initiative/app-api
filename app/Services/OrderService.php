<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticketing\Cart;
use App\Models\Ticketing\Order;
use Illuminate\Database\Eloquent\Collection;
use Stripe\Checkout\Session;

class OrderService
{
    public function __construct(protected StripeService $stripeService)
    {
    }

    public function assertSessionDoesntHaveOrder(string $sessionId): void
    {
        abort_if(Order::query()->stripeCheckoutId($sessionId)->exists(), 422, 'Checkout session already processed');
    }

    public function createOrderFromSession(Session $session): Order
    {
        $this->assertSessionDoesntHaveOrder($session->id);
        $cart = Cart::query()->stripeCheckoutId($session->id)->firstOrFail();
        $data = array_merge(
            $this->mapDataFromSession($session),
            $this->mapDataFromCart($cart),
        );

        return Order::create($data);
    }

    /**
     * @return array<string, string|int>
     */
    protected function mapDataFromSession(Session $session): array
    {
        return [
            'user_email' => $session->customer_email,
            'amount_subtotal' => $session->amount_subtotal,
            'amount_total' => $session->amount_total,
            // @phpstan-ignore-next-line
            'amount_tax' => $session->total_details->amount_tax,
            'stripe_checkout_id' => $session->id,
        ];
    }

    /**
     * @return array<string, string|int|array<string, string|int>>
     */
    protected function mapDataFromCart(Cart $cart): array
    {
        return [
            'user_id' => $cart->user_id,
            'event_id' => $cart->event->id,
            'cart_id' => $cart->id,
            'quantity' => $cart->quantity,
            'ticket_data' => $this->jsonFromCartItems($cart->items),
        ];
    }

    /**
     * @param  Collection<int, \App\Models\Ticketing\CartItem>  $items
     * @return array<string, string|int>
     */
    protected function jsonFromCartItems(Collection $items): array
    {
        return $items->each->setVisible([
            'id',
            'ticket_type_id',
            'reserved_ticket_id',
            'quantity',
        ])->toArray();
    }
}
