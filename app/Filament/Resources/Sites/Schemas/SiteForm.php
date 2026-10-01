<?php

namespace App\Filament\Resources\Sites\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                Section::make('Cím')
                    ->columns(2)
                    ->schema([
                        TextInput::make('country')
                            ->label('Ország'),
                        TextInput::make('city')
                            ->label('Város'),
                        TextInput::make('zip')
                            ->label('Irányítószám'),
                        TextInput::make('address')
                            ->label('Cím'),
                    ]),
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true)
                    ->required(),
                Toggle::make('is_main')
                    ->label('Fő telephely')
                    ->helperText('Az új megrendelések mindig a fő telephelyre érkeznek be. Egyszerre csak egy telephely lehet fő telephely.')
                    ->default(false)
                    ->required(),
            ]);
    }
}
