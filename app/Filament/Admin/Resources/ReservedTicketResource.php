<?php
declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReservedTicketResource\Pages;
use App\Filament\Admin\Resources\ReservedTicketResource\RelationManagers;
use App\Models\Ticketing\ReservedTicket;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReservedTicketResource extends Resource
{
    protected static ?string $model = ReservedTicket::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Event Specific';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('ticket_type_id')
                    ->relationship(
                        name: 'ticketType', 
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('event_id', session('active_event_id', 0)),
                    )
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'display_name')
                    ->searchable(['display_name', 'email'])
                    ->getOptionLabelFromRecordUsing(fn (User $user) => "{$user->display_name} ({$user->email})"),
                Forms\Components\DateTimePicker::make('expiration_date'),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255),
            ]);
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
                Tables\Columns\TextColumn::make('ticketType.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListReservedTickets::route('/'),
            'create' => Pages\CreateReservedTicket::route('/create'),
            'view' => Pages\ViewReservedTicket::route('/{record}'),
            'edit' => Pages\EditReservedTicket::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereRelation('ticketType', 'event_id', session('active_event_id', 0));
    }
}
