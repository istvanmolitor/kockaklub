<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegionStocksRelationManager extends RelationManager
{
    protected static string $relationship = 'regionStocks';

    protected static ?string $title = 'Régiónkénti készlet';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('region.site.name')
                    ->label('Telephely'),
                TextColumn::make('region.name')
                    ->label('Régió'),
                IconColumn::make('region.is_public')
                    ->label('Publikus')
                    ->boolean(),
                TextColumn::make('quantity')
                    ->label('Mennyiség')
                    ->numeric(),
                TextColumn::make('min_stock')
                    ->label('Minimum')
                    ->numeric()
                    ->placeholder('—'),
                TextColumn::make('max_stock')
                    ->label('Maximum')
                    ->numeric()
                    ->placeholder('—'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
