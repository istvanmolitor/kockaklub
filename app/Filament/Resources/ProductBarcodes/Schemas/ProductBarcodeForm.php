<?php

namespace App\Filament\Resources\ProductBarcodes\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductBarcodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('barcode')
                    ->label('Vonalkód')
                    ->required()
                    ->unique(ignoreRecord: true),
                Repeater::make('productPivots')
                    ->label('Termékek')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Termék')
                            ->relationship(name: 'product', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_primary')
                            ->label('Elsődleges')
                            ->fixIndistinctState(),
                    ])
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => Product::find($state['product_id'] ?? null)?->name)
                    ->defaultItems(0)
                    ->addActionLabel('Termék hozzáadása')
                    ->columnSpanFull(),
            ]);
    }
}
