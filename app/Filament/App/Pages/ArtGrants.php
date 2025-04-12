<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Models\Event;
use App\Models\Grants\ArtProject;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Validator;
use Livewire\Attributes\Validate;

// use Illuminate\Support\Collection;

class ArtGrants extends Page
{
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static string $view = 'filament.app.pages.art-grants';

    protected static ?string $navigationLabel = 'Art Grant Voting';

    public EloquentCollection $projects;
    
    /** @var array<int,int> */
    public array $selectedProjects;

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

    public function boot(): void
    {
        $this->withValidator(function (Validator $validator) {
            $validator->after(function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    Notification::make()
                        ->title('Error')
                        ->body($validator->errors()->first())
                        ->status('danger')
                        ->color('danger')
                        ->send();
                }
            });
        });
    }

    public function mount(): void
    {
        $this->projects = ArtProject::query()->currentEvent()->approved()->get();
        $this->selectedProjects = [];//collect();
        $this->remainingVotes = $this->maxVotes - count($this->selectedProjects);
    }

    public function updated(): void
    {
        $this->remainingVotes = $this->maxVotes - count($this->selectedProjects);
    }

    public function save(): void
    {
        $this->validate([
            'selectedProjects' => [
                'required',
                'array',
                fn($attribute, $value, $fail) => array_sum($value) > $this->maxVotes
                    ? $fail('You cannot submit more than  ' . $this->maxVotes . ' votes.'): null,
                function ($attribute, $value, $fail) {
                    foreach(array_keys($value) as $key) {
                        if (! ArtProject::find($key)) {
                            $fail('Invalid project ID: ' . $key);
                        }
                    }
                },
            ],
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
