<?php

namespace App\Filament\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kód')
                    ->required()
                    ->length(3)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                TextInput::make('sort_order')
                    ->label('Sorrend')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_default')
                    ->label('Alapértelmezett'),
            ]);
    }
}
