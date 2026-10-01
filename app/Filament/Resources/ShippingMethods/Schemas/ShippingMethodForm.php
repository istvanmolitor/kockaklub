<?php

namespace App\Filament\Resources\ShippingMethods\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true)
                    ->required(),
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
