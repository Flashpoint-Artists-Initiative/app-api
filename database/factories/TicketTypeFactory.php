<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(),
            'sale_start_date' => fake()->dateTimeInInterval('-1 month'),
            'sale_end_date' => fake()->dateTimeInInterval('+1 month'),
            'quantity' => fake()->numberBetween(100, 300),
            'price' => fake()->numberBetween(20, 100),
            'description' => fake()->paragraph(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function zeroQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
        ]);
    }

    public function onSaleInFuture(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_start_date' => fake()->dateTimeInInterval('+1 month'),
            'sale_end_date' => fake()->dateTimeInInterval('+2 months'),
        ]);
    }

    public function onSaleInPast(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_start_date' => fake()->dateTimeInInterval('-2 months'),
            'sale_end_date' => fake()->dateTimeInInterval('-1 month'),
        ]);
    }
}
