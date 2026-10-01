<?php

namespace App\Filament\Resources\OrderStatuses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OrderStatusForm
{
    public const COLORS = [
        'gray' => 'Szürke',
        'info' => 'Kék (info)',
        'success' => 'Zöld (success)',
        'warning' => 'Sárga (warning)',
        'danger' => 'Piros (danger)',
        'primary' => 'Elsődleges (primary)',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('color')
                    ->options(self::COLORS)
                    ->required()
                    ->default('gray'),
                TextInput::make('sort_order')
                    ->label('Sorrend')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_default')
                    ->label('Alapértelmezett új rendelésen'),
                Toggle::make('is_final')
                    ->label('Lezárt állapot'),
            ]);
    }
}
