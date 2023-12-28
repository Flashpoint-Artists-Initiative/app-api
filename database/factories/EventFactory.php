<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'location' => fake()->streetAddress(),
            'start_date' => fake()->dateTimeBetween('-1 week'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'contact_email' => fake()->safeEmail(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }

    public function offset(string $offset): static
    {
        return $this->state(function (array $attributes) use ($offset) {
            $start_date = fake()->dateTimeInInterval($offset, '+1 year');

            return [
                'start_date' => $start_date,
                'end_date' => $start_date->modify('+2 weeks'),
            ];
        });
    }
}
