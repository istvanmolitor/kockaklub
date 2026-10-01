<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\StockMovement;
use App\Repositories\ProductRepository;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Típus')
                    ->options(StockMovement::types())
                    ->required()
                    ->live(),
                Select::make('source_region_id')
                    ->label('Forrás régió')
                    ->relationship('sourceRegion', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true)),
                Select::make('destination_region_id')
                    ->label('Cél régió')
                    ->relationship('destinationRegion', 'name')
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->rule(fn (Get $get) => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                        if ($get('type') === StockMovement::TYPE_TRANSFER && $value && $value === $get('source_region_id')) {
                            $fail('Átadásnál a forrás és a cél régió nem lehet azonos.');
                        }
                    }),
                DatePicker::make('movement_date')
                    ->label('Dátum')
                    ->required()
                    ->default(now()),
                Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull(),
                Repeater::make('items')
                    ->label('Tételek')
                    ->relationship()
                    ->schema([
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
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Tétel hozzáadása')
                    ->columnSpanFull(),
            ]);
    }
}
