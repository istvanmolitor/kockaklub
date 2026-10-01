<?php

namespace App\Filament\Resources\Sites\RelationManagers;

use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RegionsRelationManager extends RelationManager
{
    protected static string $relationship = 'regions';

    protected static ?string $title = 'Régiók';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                Toggle::make('is_public')
                    ->label('Publikus')
                    ->default(true)
                    ->required(),
                Repeater::make('regionProductStocks')
                    ->relationship()
                    ->label('Termékenkénti minimum / maximum készlet')
                    ->schema([
                        Select::make('product_id')
                            ->label('Termék')
                            ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('quantity')
                            ->label('Aktuális mennyiség')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('min_stock')
                            ->label('Minimum')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('max_stock')
                            ->label('Maximum')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->addActionLabel('Termék hozzáadása')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label('Publikus')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
