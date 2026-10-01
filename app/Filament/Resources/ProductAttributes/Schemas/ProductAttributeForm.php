<?php

namespace App\Filament\Resources\ProductAttributes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductAttributeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Azonosító')
                    ->required()
                    ->unique(ignoreRecord: true),
                Toggle::make('allow_multiple')
                    ->label('Több érték is választható')
                    ->required(),
                Repeater::make('values')
                    ->relationship()
                    ->label('Értékek')
                    ->schema([
                        TextInput::make('value')
                            ->label('Érték')
                            ->required(),
                    ])
                    ->addActionLabel('Érték hozzáadása')
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]);
    }
}
