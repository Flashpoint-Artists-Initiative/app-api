<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WaiverResource\Pages;
use App\Filament\Admin\Resources\WaiverResource\RelationManagers;
use App\Models\Event;
use App\Models\Ticketing\Waiver;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WaiverResource extends Resource
{
    protected static ?string $model = Waiver::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationGroup = 'Event Specific';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    Section::make([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                    Section::make([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->default(Event::getCurrentEventId()),
                        Forms\Components\Toggle::make('minor_waiver')
                            ->required(),
                        Forms\Components\Placeholder::make('created_at')
                            ->content(fn (?Waiver $record): ?string => $record?->created_at?->format('Y-m-d H:i:s'))
                            ->hidden(fn(string $operation) => $operation === 'create'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->content(fn (?Waiver $record): ?string => $record?->updated_at?->format('Y-m-d H:i:s'))
                            ->hidden(fn(string $operation, ?Waiver $record) => $operation === 'create' || $record?->updated_at == $record?->created_at),
                            ])->grow(false),
                ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\IconColumn::make('minor_waiver')
                    ->boolean(),
                Tables\Columns\TextColumn::make('completed_waivers_count')
                    ->counts('completedWaivers')
                    ->label('# Completed Waivers'),
            ])
            ->filters([
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
            RelationManagers\CompletedWaiversRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaivers::route('/'),
            'create' => Pages\CreateWaiver::route('/create'),
            'view' => Pages\ViewWaiver::route('/{record}'),
            'edit' => Pages\EditWaiver::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('event_id', Event::getCurrentEventId());
    }
}
