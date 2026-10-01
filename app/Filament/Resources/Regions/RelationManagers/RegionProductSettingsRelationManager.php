<?php

namespace App\Filament\Resources\Regions\RelationManagers;

use App\Models\RegionProductSetting;
use App\Repositories\ProductRepository;
use App\Repositories\StockRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegionProductSettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'productSettings';

    protected static ?string $title = 'Termékenkénti beállítások';

    protected static ?string $modelLabel = 'Termékbeállítás';

    protected static ?string $pluralModelLabel = 'Termékbeállítások';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Termék')
                    ->options(fn () => app(ProductRepository::class)->pluckNamesForSelect())
                    ->searchable()
                    ->required(),
                TextInput::make('min_stock')
                    ->label('Minimum')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('max_stock')
                    ->label('Maximum')
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => $record->product?->name ?? '')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Termék')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Aktuális mennyiség')
                    ->state(fn (RegionProductSetting $record) => app(StockRepository::class)->quantityFor($record->region_id, $record->product_id))
                    ->numeric(),
                TextColumn::make('min_stock')
                    ->label('Minimum')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_stock')
                    ->label('Maximum')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Termék hozzáadása'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
