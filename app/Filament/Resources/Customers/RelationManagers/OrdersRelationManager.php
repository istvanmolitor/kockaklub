<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Rendelésszám')
                    ->searchable(),
                TextColumn::make('orderStatus.name')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus->color),
                TextColumn::make('total')
                    ->label('Összeg')
                    ->money('HUF', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dátum')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
