<?php

namespace App\Filament\Resources\PurchaseStatuses;

use App\Filament\Resources\PurchaseStatuses\Pages\CreatePurchaseStatus;
use App\Filament\Resources\PurchaseStatuses\Pages\EditPurchaseStatus;
use App\Filament\Resources\PurchaseStatuses\Pages\ListPurchaseStatuses;
use App\Filament\Resources\PurchaseStatuses\Schemas\PurchaseStatusForm;
use App\Filament\Resources\PurchaseStatuses\Tables\PurchaseStatusesTable;
use App\Models\PurchaseStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseStatusResource extends Resource
{
    protected static ?string $model = PurchaseStatus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Beszerzés';

    protected static ?string $modelLabel = 'Beszerzés státusz';

    protected static ?string $pluralModelLabel = 'Beszerzés státuszok';

    public static function form(Schema $schema): Schema
    {
        return PurchaseStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseStatusesTable::configure($table);
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
            'index' => ListPurchaseStatuses::route('/'),
            'create' => CreatePurchaseStatus::route('/create'),
            'edit' => EditPurchaseStatus::route('/{record}/edit'),
        ];
    }
}
