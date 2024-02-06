<?php

namespace Database\Factories\Ticketing;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Waiver>
 */
class WaiverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->bs(),
            'content' => fake()->paragraphs(asText: true),
            'event_id' => Event::factory(),
        ];
    }

    public function minorWaiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'minor_waiver' => true,
        ]);
    }
}
