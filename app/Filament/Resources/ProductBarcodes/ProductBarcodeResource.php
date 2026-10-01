<?php

namespace App\Filament\Resources\ProductBarcodes;

use App\Filament\Resources\ProductBarcodes\Pages\CreateProductBarcode;
use App\Filament\Resources\ProductBarcodes\Pages\EditProductBarcode;
use App\Filament\Resources\ProductBarcodes\Pages\ListProductBarcodes;
use App\Filament\Resources\ProductBarcodes\Schemas\ProductBarcodeForm;
use App\Filament\Resources\ProductBarcodes\Tables\ProductBarcodesTable;
use App\Models\ProductBarcode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductBarcodeResource extends Resource
{
    protected static ?string $model = ProductBarcode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string|\UnitEnum|null $navigationGroup = 'Termék';

    protected static ?string $modelLabel = 'Vonalkód';

    protected static ?string $pluralModelLabel = 'Vonalkódok';

    public static function form(Schema $schema): Schema
    {
        return ProductBarcodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductBarcodesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductBarcodes::route('/'),
            'create' => CreateProductBarcode::route('/create'),
            'edit' => EditProductBarcode::route('/{record}/edit'),
        ];
    }
}
