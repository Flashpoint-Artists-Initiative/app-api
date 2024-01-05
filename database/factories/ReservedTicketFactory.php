<?php

namespace Database\Factories;

use App\Models\PurchasedTicket;
use App\Models\ReservedTicket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservedTicket>
 */
class ReservedTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [];
    }

    public function withEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => fake()->safeEmail(),
        ]);
    }

    public function expirationDateInPast(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeInInterval('-2 weeks', '-1 day'),
        ]);
    }

    public function expirationDateInFuture(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiration_date' => fake()->dateTimeInInterval('+1 weeks', '+1 week'),
        ]);
    }

    public function withPurchasedTicket(): static
    {
        return $this->forUser()->afterCreating(function (ReservedTicket $reservedTicket) {
            $purchasedTicket = PurchasedTicket::factory()->for($reservedTicket->ticketType)->create();
            $reservedTicket->purchased_ticket_id = $purchasedTicket->id;
            $reservedTicket->save();
        });
    }
}
