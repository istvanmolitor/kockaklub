<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                RichEditor::make('description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->label('Ár')
                    ->required()
                    ->numeric()
                    ->suffix('Ft'),
                TextInput::make('stock')
                    ->label('Készlet')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sku')
                    ->label('SKU'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Repeater::make('images')
                    ->relationship()
                    ->reorderable()
                    ->orderColumn('sort_order')
                    ->schema([
                        FileUpload::make('path')
                            ->label('Kép')
                            ->image()
                            ->disk('public')
                            ->directory('product-images')
                            ->required(),
                        TextInput::make('alt_text')
                            ->label('Alt szöveg'),
                        Toggle::make('is_default')
                            ->label('Alapértelmezett')
                            ->fixIndistinctState(),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->columnSpanFull(),
            ]);
    }
}
