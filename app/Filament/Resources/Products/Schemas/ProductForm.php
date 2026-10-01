<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductAttribute;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
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
                Section::make('Tulajdonságok')
                    ->schema(static::attributeValueFields())
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn () => ProductAttribute::query()->exists()),
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

    /**
     * @return array<int, Select>
     */
    protected static function attributeValueFields(): array
    {
        return ProductAttribute::query()
            ->with('values')
            ->orderBy('name')
            ->get()
            ->map(fn (ProductAttribute $attribute) => Select::make("attribute_values.{$attribute->id}")
                ->label($attribute->name)
                ->multiple($attribute->allow_multiple)
                ->options($attribute->values->pluck('value', 'id'))
                ->searchable()
                ->preload())
            ->all();
    }

    /**
     * Extracts the selected product attribute value IDs from submitted form data.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, int>
     */
    public static function extractAttributeValueIds(array $data): array
    {
        return collect(Arr::get($data, 'attribute_values', []))
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Builds the `attribute_values.{attribute_id}` form state for an existing product.
     *
     * @return array<int, int|array<int, int>>
     */
    public static function mapAttributeValuesForFill(Product $product): array
    {
        return $product->attributeValues()
            ->with('attribute')
            ->get()
            ->groupBy('product_attribute_id')
            ->map(function ($values) {
                $ids = $values->pluck('id')->all();

                return $values->first()->attribute->allow_multiple ? $ids : ($ids[0] ?? null);
            })
            ->all();
    }
}
