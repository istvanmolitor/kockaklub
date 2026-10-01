<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Termék'),
                TextColumn::make('unit_price')
                    ->label('Egységár')
                    ->money('HUF', decimalPlaces: 0),
                TextColumn::make('quantity')
                    ->label('Mennyiség'),
                TextColumn::make('line_total')
                    ->label('Összesen')
                    ->money('HUF', decimalPlaces: 0),
            ]);
    }
}
