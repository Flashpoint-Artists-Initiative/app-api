<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Models\Grants\ProjectImage;
use Illuminate\Database\Seeder;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;

class ArtProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $event = Event::where('active', true)->first();
        $event->settings['voting_enabled'] = true;
        $event->save();
        // $inactive = Event::where('active', false)->first();

        ArtProject::factory()->for($event)->count(2)->create();
        ArtProject::factory()->for($event)->state(['project_status' => 'approved'])->create();
        ArtProject::factory()->for($event)->state(['project_status' => 'denied'])->create();

        // ArtProject::factory()->for($inactive)->create();
        // ArtProject::factory()->for($inactive)->state(['project_status' => 'pending-review'])->create();
        // ArtProject::factory()->for($inactive)->state(['project_status' => 'denied'])->create();

        $project = ArtProject::first();

        ProjectImage::factory()->for($project)->count(3)->create();
    }
}
