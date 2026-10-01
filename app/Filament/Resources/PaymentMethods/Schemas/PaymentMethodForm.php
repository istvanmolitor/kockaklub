<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
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
                Select::make('shippingMethods')
                    ->label('Szállítási módok')
                    ->relationship('shippingMethods', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
