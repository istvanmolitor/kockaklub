<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Típus')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => StockMovement::types()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        StockMovement::TYPE_IN => 'success',
                        StockMovement::TYPE_OUT => 'danger',
                        StockMovement::TYPE_TRANSFER => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('sourceRegion.name')
                    ->label('Forrás régió')
                    ->placeholder('—'),
                TextColumn::make('destinationRegion.name')
                    ->label('Cél régió')
                    ->placeholder('—'),
                TextColumn::make('movement_date')
                    ->label('Dátum')
                    ->date()
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Tételek'),
                TextColumn::make('closed_at')
                    ->label('Állapot')
                    ->badge()
                    ->state(fn (StockMovement $record) => $record->isClosed() ? 'Lezárva' : 'Nyitott')
                    ->color(fn (StockMovement $record) => $record->isClosed() ? 'success' : 'gray'),
                TextColumn::make('createdBy.name')
                    ->label('Létrehozta')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('closedBy.name')
                    ->label('Lezárta')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Típus')
                    ->options(StockMovement::types()),
                TernaryFilter::make('closed_at')
                    ->label('Állapot')
                    ->placeholder('Mind')
                    ->trueLabel('Lezárva')
                    ->falseLabel('Nyitott')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('closed_at'),
                        false: fn ($query) => $query->whereNull('closed_at'),
                    ),
            ])
            ->recordActions([
                Action::make('close')
                    ->label('Lezárás')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->visible(fn (StockMovement $record) => ! $record->isClosed())
                    ->requiresConfirmation()
                    ->modalDescription('Lezárás után a mozgatás módosítja a készletet, és a tételek már nem szerkeszthetők.')
                    ->action(function (StockMovement $record) {
                        try {
                            app(StockService::class)->closeMovement($record);

                            Notification::make()
                                ->title('A készlet mozgatás lezárva')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('A lezárás sikertelen')
                                ->body(implode(' ', $exception->validator->errors()->all()))
                                ->danger()
                                ->send();
                        }
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => app(StockService::class)->rebuildRegionProductStocks()),
                ]),
            ]);
    }
}
