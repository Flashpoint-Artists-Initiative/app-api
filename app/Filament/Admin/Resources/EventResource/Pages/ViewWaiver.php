<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EventResource\Pages;

use App\Filament\Admin\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\Event;

class ViewWaiver extends ViewRecord
{
    protected static string $resource = EventResource::class;

    protected static ?string $navigationLabel = 'Waiver';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.resources.event-resource.pages.view-waiver';

    public bool $hasWaiver = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->url(EditWaiver::getUrl(['record' => $this->record]))
                ->label(fn() => $this->hasWaiver ? 'Edit Waiver' : 'Create Waiver')
                ->hidden(function () {
                    /** @var Event $event */
                    $event = $this->record;
                    return $event->completedWaivers()->exists();
                }),
        ];
    }
    
    public function getBreadcrumb(): string
    {
        return 'Waiver';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->columnSpanFull(),
                ])
                ->relationship('waiver'),
            ]);
    } 

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Event $model */
        $model = $this->getRecord();

        $this->hasWaiver = $model->waiver !== null;
    }
}
