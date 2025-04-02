<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Ticketing\Cart;
use App\Models\Ticketing\CartItem;
use App\Models\Ticketing\Order;
use App\Services\StripeService;
use Illuminate\Database\Seeder;

class AccurateOrderSeeder extends Seeder
{
    /**
     * Create accurate order data
     */
    public function run(): void
    {
        $stripeService = app()->make(StripeService::class);

        $event = Event::has('ticketTypes')->withCount('ticketTypes')->orderByDesc('ticket_types_count')->first();
        $ticketTypes = $event->ticketTypes;

        for ($i = 0; $i < 30; $i++) {
            $tickets = $ticketTypes->random(fake()->numberBetween(1, 3));
            $ticketCount = $tickets->count();
            $quantity = fake()->numberBetween($ticketCount, 4);
            $totalQuantity = $quantity;
            $amountSubtotal = 0;
            $amountTax = 0;

            $cart = Cart::factory()->create();

            $ticketData = [];
            $j = 0;
            foreach ($tickets as $ticket) {
                $j++;

                if ($j === $ticketCount) {
                    $itemQuantity = $quantity;
                } else {
                    $itemQuantity = 1;
                    $quantity--;
                }

                $data = [
                    'cart_id' => $cart->id,
                    'ticket_type_id' => $ticket->id,
                    'quantity' => $itemQuantity,
                ];

                $amountSubtotal += $itemQuantity * $ticket->price;

                $cartItem = CartItem::factory()->create($data);
                $data['id'] = $cartItem->id;
                unset($data['user_id']);

                $ticketData[] = $data;
            }

            $amountTax = $stripeService->calculateTax($amountSubtotal);
            $amountFees = $stripeService->calculateFees($amountSubtotal);

            Order::factory()->createQuietly([
                'user_id' => $cart->user_id,
                'event_id' => $event->id,
                'cart_id' => $cart->id,
                'quantity' => $totalQuantity,
                'ticket_data' => $ticketData,
                'amount_subtotal' => $amountSubtotal,
                'amount_tax' => $amountTax,
                'amount_fees' => $amountFees,
                'amount_total' => $amountSubtotal + $amountTax + $amountFees,
                'created_at' => fake()->dateTimeBetween(now()->subMonth()),
            ]);
        }
    }
}
