<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Livewire\OrderTicketsTable;
use App\Models\Event;
use App\Models\Ticketing\Order;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        /** @var Order $record */
        return $record ? "Order #{$record->id}" : static::getModelLabel();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make([
                        Livewire::make(OrderTicketsTable::class),
                        Fieldset::make('Order Summary')
                            ->schema([
                                TextEntry::make('amount_subtotal')
                                    ->label('Subtotal')
                                    ->money('USD', 100),
                                TextEntry::make('amount_tax')
                                    ->label('Tax')
                                    ->money('USD', 100),
                                TextEntry::make('amount_total')
                                    ->label('Total')
                                    ->money('USD', 100),
                            ])->columns(3),
                    ]),
                    Section::make([
                        TextEntry::make('created_at'),
                        TextEntry::make('user.display_name')
                            ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record->user_id]))
                            ->color('primary')
                            ->icon('heroicon-m-user')
                            ->iconColor('primary')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('user_email'),
                        TextEntry::make('event.name')
                            ->url(fn ($record) => EventResource::getUrl('view', ['record' => $record->event_id]))
                            ->color('primary')
                            ->icon('heroicon-m-calendar')
                            ->iconColor('primary')
                            ->weight(FontWeight::Bold),
                        // TextEntry::make('purchasedTickets.id')
                        //     ->badge(),
                    ])->grow(false),
                ])->from('lg'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.display_name')
                    ->searchable(['users.display_name', 'users.email'])
                    ->sortable()
                    ->url(fn ($record) => UserResource::getUrl('view', ['record' => $record->user_id]))
                    ->color('primary')
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('event.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),
                Tables\Columns\TextColumn::make('amount_tax')
                    ->numeric()
                    ->sortable()
                    ->money('USD', 100),
                Tables\Columns\TextColumn::make('amount_total')
                    ->numeric()
                    ->sortable()
                    ->money('USD', 100),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->relationship('event', 'name'),
                Filter::make('created_after')
                    ->form([
                        DatePicker::make('created_after'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                        ->when(
                            $data['created_after'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['created_after']) {
                            return null;
                        }
                 
                        return 'Created after ' . Carbon::parse($data['created_after'])->toFormattedDateString();
                    }),
                    Filter::make('created_before')
                        ->form([
                            DatePicker::make('created_before'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                            ->when(
                                $data['created_before'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                        })
                        ->indicateUsing(function (array $data): ?string {
                            if (! $data['created_before']) {
                                return null;
                            }
                     
                            return 'Created before ' . Carbon::parse($data['created_before'])->toFormattedDateString();
                        }),
            ], FiltersLayout::AboveContent)
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
