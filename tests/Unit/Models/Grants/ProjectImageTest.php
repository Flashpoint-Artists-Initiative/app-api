<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Grants;

use App\Models\Grants\ArtProject;
use App\Models\Grants\ProjectImage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectImageTest extends TestCase
{
    /**
     * Test that a ProjectImage can be created.
     */
    #[Test]
    public function project_image_can_be_created(): void
    {
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create();
        $projectImage = ProjectImage::create([
            'art_project_id' => $artProject->id,
            'name' => 'some-name',
            'path' => 'some-path',
        ]);

        $this->assertDatabaseHas('project_images', [
            'id' => $projectImage->id,
            'art_project_id' => $artProject->id,
            'name' => 'some-name',
            'path' => 'some-path',
        ]);
    }

    /**
     * Test the relationship between ProjectImage and ArtProject.
     */
    #[Test]
    public function project_image_belongs_to_project(): void
    {
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create();
        /** @var ProjectImage $projectImage */
        $projectImage = ProjectImage::factory()->create(['art_project_id' => $artProject->id]);

        $this->assertInstanceOf(ArtProject::class, $projectImage->artProject);
        $this->assertEquals($artProject->id, $projectImage->artProject->id);
    }
}
