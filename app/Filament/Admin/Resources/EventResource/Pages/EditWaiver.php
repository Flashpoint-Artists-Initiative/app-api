<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EventResource\Pages;

use App\Filament\Admin\Resources\EventResource;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use App\Models\Event;
use Filament\Actions\Action;

class EditWaiver extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make('delete')
                // ->label('Delete Waiver')
                ->hidden(function () { 
                    /** @var Event $event */
                    $event = $this->record;
                    return !$event->waiver || $event->completedWaivers()->count() > 0;
                })
                ->action(function (Action $action) {
                    /** @var Event $event */
                    $event = $this->record;
                    if ($event->waiver?->delete()) {
                        $action->success();
                    } else {
                        $action->failure();
                    }
                    
                })
                ->modalHeading('Delete Waiver')
                // ->successNotificationTitle('Deleted')
                // ->color('danger')
                // ->requiresConfirmation()
                ->successRedirectUrl(ViewEvent::getUrl(['record' => $this->record])),
        ];
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
}
