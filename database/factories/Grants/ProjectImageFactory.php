<?php

declare(strict_types=1);

namespace Database\Factories\Grants;

use App\Models\Grants\ArtProject;
use App\Models\Grants\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectImageFactory extends Factory
{
    protected $model = ProjectImage::class;

    public function definition()
    {
        return [
            'art_project_id' => ArtProject::factory(),
            'name' => $this->faker->word,
            'path' => $this->faker->filePath,
        ];
    }
}
