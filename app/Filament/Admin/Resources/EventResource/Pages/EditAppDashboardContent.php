<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\EventResource\Pages;

use App\Enums\PageContentEnum;
use App\Filament\Admin\Resources\EventResource;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;

/**
 * If more pages are added later, modify this class to be extendable for multiple pages
 */
class EditAppDashboardContent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected static ?string $navigationLabel = 'Dashboard Content';

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket-square';

    public function getBreadcrumb(): string
    {
        return 'Dashboard Content';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->disableToolbarButtons(['attachFiles'])
                        ->columnSpanFull(),
                ])
                    ->relationship('dashboardContent')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => $this->mutateData($data)),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateData(array $data): array
    {
        $data['page'] = PageContentEnum::AppDashboard;

        return $data;
    }
}
