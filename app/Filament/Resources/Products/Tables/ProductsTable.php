<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withPublicStock())
            ->columns([
                ImageColumn::make('default_image_url')
                    ->label('Kép')
                    ->square()
                    ->checkFileExistence(false),
                TextColumn::make('category.name')
                    ->label('Kategória')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Ár')
                    ->money('HUF', decimalPlaces: 0)
                    ->sortable(),
                IconColumn::make('public_stock')
                    ->label('Készleten')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (Product $record) => $record->public_stock > 0),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktív')
                    ->boolean(),
                IconColumn::make('is_discontinued')
                    ->label('Kifutó')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Kiemelt')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Módosítva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
