<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
