<?php

namespace App\Filament\Resources\Regions\Schemas;

use App\Models\Site;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('site_id')
                    ->label('Telephely')
                    ->options(fn () => Site::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                Toggle::make('is_public')
                    ->label('Publikus')
                    ->default(true)
                    ->required(),
            ]);
    }
}
