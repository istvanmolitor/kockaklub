<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'priceLogs';

    protected static ?string $title = 'Ár előzmények';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('previous_price')
                    ->label('Előző ár')
                    ->money('HUF', decimalPlaces: 0)
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('Új ár')
                    ->money('HUF', decimalPlaces: 0),
                TextColumn::make('user.name')
                    ->label('Módosította')
                    ->placeholder('Rendszer'),
                TextColumn::make('created_at')
                    ->label('Dátum')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([]);
    }
}
