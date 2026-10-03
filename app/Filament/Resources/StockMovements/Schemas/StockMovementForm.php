<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Models\Region;
use App\Models\Site;
use App\Models\StockMovement;
use App\Repositories\ProductRepository;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->live()
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
                DatePicker::make('movement_date')
                    ->label('Dátum')
                    ->required()
                    ->default(now())
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
                Select::make('source_site_id')
                    ->label('Forrás telephely')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, ?StockMovement $record) {
                        if ($record?->sourceRegion) {
                            $component->state($record->sourceRegion->site_id);
                        }
                    })
                    ->afterStateUpdated(fn (Set $set) => $set('source_region_id', null))
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true))
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
                Select::make('source_region_id')
                    ->label('Forrás régió')
                    ->options(fn (Get $get) => Region::query()->where('site_id', $get('source_site_id'))->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true))
                    ->disabled(fn (Get $get, ?StockMovement $record) => $record?->isClosed() || blank($get('source_site_id'))),
                Select::make('destination_site_id')
                    ->label('Cél telephely')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, ?StockMovement $record) {
                        if ($record?->destinationRegion) {
                            $component->state($record->destinationRegion->site_id);
                        }
                    })
                    ->afterStateUpdated(fn (Set $set) => $set('destination_region_id', null))
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
                Select::make('destination_region_id')
                    ->label('Cél régió')
                    ->options(fn (Get $get) => Region::query()->where('site_id', $get('destination_site_id'))->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->visible(fn (Get $get) => in_array($get('type'), [StockMovement::TYPE_IN, StockMovement::TYPE_TRANSFER], true))
                    ->disabled(fn (Get $get, ?StockMovement $record) => $record?->isClosed() || blank($get('destination_site_id')))
                    ->rule(fn (Get $get) => function (string $attribute, mixed $value, Closure $fail) use ($get) {
                        if ($get('type') === StockMovement::TYPE_TRANSFER && $value && $value === $get('source_region_id')) {
                            $fail('Átadásnál a forrás és a cél régió nem lehet azonos.');
                        }
                    }),
                Textarea::make('note')
                    ->label('Megjegyzés')
                    ->columnSpanFull()
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
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
                    ->columnSpanFull()
                    ->disabled(fn (?StockMovement $record) => $record?->isClosed()),
            ]);
    }
}
