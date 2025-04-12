<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Models\Event;
use App\Models\Grants\ArtProject;
use App\Rules\ArtProjectVotingRule;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

/**
 * @property Form $form
 */
class ArtGrants extends Page
{
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static string $view = 'filament.app.pages.art-grants';

    protected static ?string $navigationLabel = 'Art Grant Voting';

    /** @var array<mixed> */
    public array $votes;

    public int $maxVotes;

    public static function shouldRegisterNavigation(): bool
    {
        return Event::getCurrentEvent()->votingEnabled ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        $eventName = Event::getCurrentEvent()->name ?? '';

        return $eventName . ' Art Grant Voting';
    }

    public function form(Form $form): Form
    {
        $projects = ArtProject::query()->currentEvent()->approved()->get();
        $finalId = $projects->last()?->id;
        $projectsSchema = $projects->map(function (ArtProject $project) use ($finalId) {
            return Forms\Components\ViewField::make('votes.' . $project->id)
                ->model($project)
                ->rule(new ArtProjectVotingRule, fn () => $project->id == $finalId) // The condition is there to prevent multiple errors from appearing
                ->hiddenLabel()
                ->default(0)
                ->view('forms.components.art-project-item');
        });

        return $form
            ->schema($projectsSchema->toArray());
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->maxVotes = Event::getCurrentEvent()->votesPerUser ?? 0;
    }

    public function submitVotes(): void
    {
        $data = $this->form->getState();
        dd($data);
    }

    public function openModal(): Action
    {
        return Action::make('projectDetailsModal')
            ->modalContent(fn ($arguments) => $this->generateModalContent($arguments));
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    /**
     * @param  array<mixed>  $arguments
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
