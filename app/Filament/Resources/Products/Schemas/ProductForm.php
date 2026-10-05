<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductBarcode;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Alapadatok')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktív')
                                    ->default(true)
                                    ->required(),
                                TextInput::make('sku')
                                    ->label('Cikkszám'),
                                Select::make('category_id')
                                    ->label('Kategória')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('name')
                                    ->label('Név')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $state, callable $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->label('Elérés')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                RichEditor::make('description')
                                    ->label('Leírás')
                                    ->columnSpanFull(),
                                TextInput::make('meta_description')
                                    ->label('Meta leírás (SEO)')
                                    ->helperText('Megjelenik a Google keresésben és AI-asszisztensekben. Üresen hagyva a leírásból generálódik.')
                                    ->maxLength(160)
                                    ->columnSpanFull(),
                                TextInput::make('price')
                                    ->label('Ár')
                                    ->required()
                                    ->numeric()
                                    ->suffix('Ft'),
                                Select::make('vat_rate')
                                    ->label('ÁFA kulcs')
                                    ->options([
                                        27 => '27%',
                                        18 => '18%',
                                        5 => '5%',
                                        0 => '0%',
                                    ])
                                    ->default(27)
                                    ->required(),
                                Toggle::make('is_discontinued')
                                    ->label('Kifutó termék')
                                    ->helperText('Kifutó termék készlethiány esetén nem rendelhető.'),
                                Toggle::make('is_featured')
                                    ->label('Kiemelt')
                                    ->helperText('Kiemelt termékek megjelennek a főoldalon.'),
                            ]),
                        Tab::make('Képek')
                            ->schema([
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
                            ]),
                        Tab::make('Vonalkódok')
                            ->schema([
                                Repeater::make('barcodePivots')
                                    ->label('Vonalkódok')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_barcode_id')
                                            ->label('Vonalkód')
                                            ->relationship(name: 'productBarcode', titleAttribute: 'barcode')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('barcode')
                                                    ->label('Vonalkód')
                                                    ->required()
                                                    ->unique(table: 'product_barcodes', column: 'barcode'),
                                            ]),
                                        Toggle::make('is_primary')
                                            ->label('Elsődleges')
                                            ->fixIndistinctState(),
                                    ])
                                    ->columns(2)
                                    ->itemLabel(fn (array $state): ?string => ProductBarcode::find($state['product_barcode_id'] ?? null)?->barcode)
                                    ->defaultItems(0)
                                    ->addActionLabel('Vonalkód hozzáadása')
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Tulajdonságok')
                            ->schema(static::attributeValueFields())
                            ->columns(2)
                            ->visible(fn () => ProductAttribute::query()->exists()),
                        Tab::make('Kapcsolódó termékek')
                            ->schema([
                                Repeater::make('relatedProductPivots')
                                    ->label('Kapcsolódó termékek')
                                    ->relationship()
                                    ->reorderable()
                                    ->orderColumn('sort_order')
                                    ->schema([
                                        Select::make('related_product_id')
                                            ->label('Termék')
                                            ->relationship(
                                                name: 'relatedProduct',
                                                titleAttribute: 'name',
                                                modifyQueryUsing: function (Builder $query, $livewire): Builder {
                                                    if ($productId = $livewire->getRecord()?->getKey()) {
                                                        $query->whereKeyNot($productId);
                                                    }

                                                    return $query;
                                                },
                                            )
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => Product::find($state['related_product_id'] ?? null)?->name)
                                    ->defaultItems(0)
                                    ->addActionLabel('Kapcsolódó termék hozzáadása')
                                    ->columnSpanFull(),
                            ]),
                    ]),
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
