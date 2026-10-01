<?php

namespace App\Filament\Resources\ProductBarcodes\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\ProductBarcode;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductBarcodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('products'))
            ->columns([
                TextColumn::make('barcode')
                    ->label('Vonalkód')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('products')
                    ->label('Termékek')
                    ->state(fn (ProductBarcode $record) => $record->products)
                    ->formatStateUsing(fn (Product $state) => $state->name)
                    ->url(fn (Product $state) => ProductResource::getUrl('edit', ['record' => $state]))
                    ->badge()
                    ->color(fn (Product $state) => $state->pivot->is_primary ? 'success' : 'gray')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->expandableLimitedList()
                    ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                        'products',
                        fn (Builder $productsQuery) => $productsQuery->where('name', 'like', "%{$search}%"),
                    )),
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
