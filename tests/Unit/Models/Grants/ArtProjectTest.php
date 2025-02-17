<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Grants;

use App\Enums\ArtProjectStatus;
use App\Enums\GrantFundingStatus;
use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Models\Grants\ProjectImage;
use App\Models\User;
use Tests\TestCase;

class ArtProjectTest extends TestCase
{
    /**
     * Test that an ArtProject can be created.
     */
    public function test_art_project_can_be_created(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $artProject = ArtProject::create([
            'name' => 'Test Project',
            'user_id' => $user->id,
            'event_id' => $event->id,
            'description' => 'Test Description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);

        $this->assertDatabaseHas('art_projects', [
            'id' => $artProject->id,
            'name' => 'Test Project',
            'user_id' => $user->id,
            'event_id' => $event->id,
            'description' => 'Test Description',
            'budget_link' => 'http://example.com/budget',
            'min_funding' => 1000,
            'max_funding' => 5000,
            'project_status' => 'pending-review',
        ]);
    }

    /**
     * Test the relationship between ArtProject and Event.
     */
    public function test_art_project_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create(['event_id' => $event->id]);

        $this->assertInstanceOf(Event::class, $artProject->event);
        $this->assertEquals($event->id, $artProject->event->id);
    }

    /**
     * Test the relationship between ArtProject and User.
     */
    public function test_art_project_belongs_to_user(): void
    {
        $user = User::factory()->create();
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $artProject->user);
        $this->assertEquals($user->id, $artProject->user->id);
    }

    /**
     * Test the relationship between ArtProject and ProjectImage.
     */
    public function test_art_project_has_many_images(): void
    {
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create();
        $projectImage = ProjectImage::factory()->create(['art_project_id' => $artProject->id]);

        $this->assertTrue($artProject->images->contains($projectImage));
    }

    /**
     * Test the relationship between ArtProject and votes.
     */
    public function test_art_project_belongs_to_many_votes(): void
    {
        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create();
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $this->assertTrue($artProject->votes->contains($user));
    }

    /**
     * Test the fundedTotal attribute.
     */
    public function test_funded_total_attribute(): void
    {
        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved,
            'event_id' => $event->id]
        );
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $this->assertEquals(10, $artProject->fundedTotal);
    }

    /**
     * Test the fundingStatus attribute.
     */
    public function test_funding_status_attribute(): void
    {
        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved,
            'event_id' => $event->id,
            'min_funding' => 10,
            'max_funding' => 20,
        ]);
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $this->assertEquals(GrantFundingStatus::MinReached, $artProject->fundingStatus);
    }

    /**
     * Test the vote method.
     */
    public function test_vote_method(): void
    {
        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved->value,
            'event_id' => $event->id]
        );
        $user = User::factory()->create();

        $artProject->vote($user);

        $this->assertTrue($artProject->votes->contains($user));
    }

    /**
     * Test the vote method when voting is closed.
     */
    public function test_vote_method_when_voting_is_closed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Grant voting is closed for this event');

        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => false,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved->value,
            'event_id' => $event->id]
        );
        $user = User::factory()->create();

        $artProject->vote($user);
    }

    /**
     * Test the vote method when project status is not approved.
     */
    public function test_vote_method_when_status_is_not_approved(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only approved projects can be voted on');

        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::PendingReview->value,
            'event_id' => $event->id]
        );
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $artProject->vote($user);
    }

    /**
     * Test the vote method when user has already voted.
     */
    public function test_vote_method_when_user_has_already_voted(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User has already voted for this project');

        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved->value,
            'event_id' => $event->id]
        );
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $artProject->vote($user);
    }

    /**
     * Test the vote method when project has reached maximum funding.
     */
    public function test_vote_method_when_project_has_reached_maximum_funding(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Project has already reached maximum funding');

        $event = Event::factory()->create([
            'settings->dollars_per_vote' => 10,
            'settings->voting_enabled' => true,
        ]);

        /** @var ArtProject $artProject */
        $artProject = ArtProject::factory()->create([
            'project_status' => ArtProjectStatus::Approved->value,
            'event_id' => $event->id,
            'max_funding' => 10,
        ]);
        $user = User::factory()->create();
        $artProject->votes()->attach($user->id);

        $secondUser = User::factory()->create();
        $artProject->vote($secondUser);
    }
}
