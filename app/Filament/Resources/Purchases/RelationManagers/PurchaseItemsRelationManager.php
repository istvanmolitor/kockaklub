<?php

namespace App\Filament\Resources\Purchases\RelationManagers;

use App\Models\PurchaseItem;
use App\Repositories\ProductRepository;
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

class PurchaseItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Tételek';

    protected static ?string $modelLabel = 'Tétel';

    protected static ?string $pluralModelLabel = 'Tételek';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Termék')
                    ->options(fn () => app(ProductRepository::class)->pluckNamesForSelect())
                    ->searchable()
                    ->required(),
                TextInput::make('quantity')
                    ->label('Mennyiség')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('unit_price')
                    ->label('Egységár')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->suffix(fn () => $this->getOwnerRecord()->currency->code)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn ($record) => $record->product?->name ?? '')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Termék')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Mennyiség')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->label('Egységár')
                    ->formatStateUsing(fn (PurchaseItem $record) => number_format((float) $record->unit_price, 4).' '.$this->getOwnerRecord()->currency->code),
                TextColumn::make('line_total')
                    ->label('Sorösszeg')
                    ->state(fn (PurchaseItem $record) => number_format($record->lineTotal(), 2).' '.$this->getOwnerRecord()->currency->code),
                TextColumn::make('unit_cost_default_currency')
                    ->label('Egységár (Ft)')
                    ->money('HUF', decimalPlaces: 0)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make(),
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
