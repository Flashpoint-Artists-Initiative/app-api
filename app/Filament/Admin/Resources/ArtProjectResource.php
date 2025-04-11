<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Enums\ArtProjectStatusEnum;
use App\Filament\Admin\Resources\ArtProjectResource\Pages;
use App\Models\Grants\ArtProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArtProjectResource extends Resource
{
    protected static ?string $model = ArtProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Event Specific';

    protected static ?string $navigationLabel = 'Art Grants';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Basic Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Project Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('artist_name')
                            ->label('Artist Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Fieldset::make('Funding')
                    ->schema([
                        Forms\Components\TextInput::make('min_funding')
                            ->label('Minimum Funding')
                            ->required()
                            ->numeric()
                            ->lte('max_funding'),
                        Forms\Components\TextInput::make('max_funding')
                            ->label('Maximum Funding')
                            ->required()
                            ->numeric()
                            ->gte('min_funding'),
                        Forms\Components\TextInput::make('budget_link')
                            ->maxLength(255),
                    ])
                    ->columns(3),
                Forms\Components\Select::make('project_status')
                    ->options(ArtProjectStatusEnum::toArray())
                    ->default(ArtProjectStatusEnum::PendingReview)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.display_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record->user_id]))
                    ->color('primary')
                    ->icon('heroicon-m-user')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('artist_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_funding')
                    ->numeric()
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_funding')
                    ->numeric()
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('budget_link')
                    ->formatStateUsing(fn () => 'View Budget')
                    ->url(fn ($record) => $record->budget_link, true)
                    ->color('primary')
                    ->icon('heroicon-m-link'),
                Tables\Columns\TextColumn::make('project_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtProjects::route('/'),
            'create' => Pages\CreateArtProject::route('/create'),
            'view' => Pages\ViewArtProject::route('/{record}'),
            'edit' => Pages\EditArtProject::route('/{record}/edit'),
        ];
    }
}
