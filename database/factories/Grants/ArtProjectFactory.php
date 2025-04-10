<?php

namespace Database\Factories\Grants;

use App\Enums\ArtProjectStatusEnum;
use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtProjectFactory extends Factory
{
    protected $model = ArtProject::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'artist_name' => $this->faker->name(),
            'description' => $this->faker->paragraph,
            'budget_link' => $this->faker->url,
            'min_funding' => $this->faker->numberBetween(1000, 5000),
            'max_funding' => $this->faker->numberBetween(6000, 10000),
            'project_status' => ArtProjectStatusEnum::PendingReview,
        ];
    }
}
