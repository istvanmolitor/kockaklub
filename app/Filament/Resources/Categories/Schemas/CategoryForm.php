<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CategoryForm
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
                Select::make('parent_id')
                    ->label('Szülő kategória')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Category $record) => $record
                            ? $query->whereNotIn('id', $record->selfAndDescendantIds())
                            : $query,
                    )
                    ->searchable()
                    ->preload(),
                Textarea::make('description')
                    ->label('Leírás')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true)
                    ->required(),
            ]);
    }
}
