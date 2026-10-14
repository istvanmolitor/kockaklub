<?php

namespace App\Filament\Resources\PurchaseCostTypes;

use App\Filament\Resources\PurchaseCostTypes\Pages\CreatePurchaseCostType;
use App\Filament\Resources\PurchaseCostTypes\Pages\EditPurchaseCostType;
use App\Filament\Resources\PurchaseCostTypes\Pages\ListPurchaseCostTypes;
use App\Filament\Resources\PurchaseCostTypes\Schemas\PurchaseCostTypeForm;
use App\Filament\Resources\PurchaseCostTypes\Tables\PurchaseCostTypesTable;
use App\Models\PurchaseCostType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseCostTypeResource extends Resource
{
    protected static ?string $model = PurchaseCostType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Beszerzés';

    protected static ?string $modelLabel = 'Költségtípus';

    protected static ?string $pluralModelLabel = 'Költségtípusok';

    public static function form(Schema $schema): Schema
    {
        return PurchaseCostTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseCostTypesTable::configure($table);
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
            'index' => ListPurchaseCostTypes::route('/'),
            'create' => CreatePurchaseCostType::route('/create'),
            'edit' => EditPurchaseCostType::route('/{record}/edit'),
        ];
    }
}
