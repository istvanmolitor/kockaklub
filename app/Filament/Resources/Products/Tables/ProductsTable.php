<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('default_image_url')
                    ->label('Kép')
                    ->square()
                    ->checkFileExistence(false),
                TextColumn::make('sku')
                    ->label('Cikkszám')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktív')
                    ->boolean(),
                TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Ár')
                    ->money('HUF', decimalPlaces: 0)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategória')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Aktív'),
                TernaryFilter::make('is_discontinued')
                    ->label('Kifutó'),
                TernaryFilter::make('is_featured')
                    ->label('Kiemelt'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Statisztika')
                    ->icon('heroicon-o-chart-bar'),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
