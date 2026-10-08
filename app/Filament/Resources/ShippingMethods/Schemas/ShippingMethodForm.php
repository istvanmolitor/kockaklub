<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use App\Enums\ShippingFulfillmentType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ShippingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                Textarea::make('description')
                    ->label('Leírás')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->label('Költség')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('Ft'),
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true)
                    ->required(),
                Select::make('fulfillment_type')
                    ->label('Teljesítés módja')
                    ->options(array_combine(
                        array_map(fn (ShippingFulfillmentType $case) => $case->value, ShippingFulfillmentType::cases()),
                        array_map(fn (ShippingFulfillmentType $case) => $case->label(), ShippingFulfillmentType::cases()),
                    ))
                    ->default(ShippingFulfillmentType::Courier->value)
                    ->live()
                    ->required(),
                Select::make('locker_provider')
                    ->label('Csomagautomata szolgáltató')
                    ->options([
                        'foxpost' => 'Foxpost',
                    ])
                    ->visible(fn (Get $get) => $get('fulfillment_type') === ShippingFulfillmentType::ParcelLocker->value)
                    ->required(fn (Get $get) => $get('fulfillment_type') === ShippingFulfillmentType::ParcelLocker->value),
                Select::make('paymentMethods')
                    ->label('Fizetési módok')
                    ->relationship('paymentMethods', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
