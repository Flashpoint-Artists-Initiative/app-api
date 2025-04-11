<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Models\Event;
use App\Models\Grants\ArtProject;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Validate;

// use Illuminate\Support\Collection;

class ArtGrants extends Page
{
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static string $view = 'filament.app.pages.art-grants';

    protected static ?string $navigationLabel = 'Art Grant Voting';

    public EloquentCollection $projects;
    
    public Collection $selectedProjects;

    public int $maxVotes = 2;
    public int $remainingVotes;

    public static function shouldRegisterNavigation(): bool
    {
        return Event::getCurrentEvent()->votingEnabled ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        $eventName = Event::getCurrentEvent()->name ?? '';

        return $eventName . ' Art Grant Voting';
    }

    public function mount(): void
    {
        $this->projects = ArtProject::query()->currentEvent()->approved()->get();
        $this->selectedProjects = collect();
        $this->remainingVotes = $this->maxVotes - count($this->selectedProjects);
    }

    public function updated(): void
    {
        $this->remainingVotes = $this->maxVotes - count($this->selectedProjects);
    }

    public function save(): void
    {
        $this->validate([
            'selectedProjects' => ['array', 'max:' . $this->maxVotes],
            'selectedProjects.*' => ['exists:art_projects,id'],
        ]);
        dd($this->selectedProjects);
    }

    public function openModal(): Action
    {
        return Action::make('projectDetailsModal')
            ->action(fn ($arguments) => dd($arguments))
            ->modalContent(fn ($arguments) => $this->generateModalContent($arguments));
    }

    /**
     * @param array<mixed> $arguments
     */
    protected function generateModalContent(array $arguments): HtmlString
    {
        $project = ArtProject::find($arguments['id']);
        
        return new HtmlString(
            Blade::render(
                '<x-art-project-modal :project=$project>Hello</x-art-project-modal>',
                ['project' => $project]
            )
        );
    }
}
