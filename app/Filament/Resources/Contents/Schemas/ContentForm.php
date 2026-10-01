<?php

namespace App\Filament\Resources\Contents\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Cím')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->label('Azonosító')
                    ->required()
                    ->unique(ignoreRecord: true),
                Repeater::make('blocks')
                    ->label('Blokkok')
                    ->relationship()
                    ->reorderable()
                    ->orderColumn('sort_order')
                    ->schema([
                        RichEditor::make('body')
                            ->label('Tartalom')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
