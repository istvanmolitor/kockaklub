<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Rendelésszám')
                    ->default(fn () => Order::generateOrderNumber())
                    ->disabled()
                    ->dehydrated(),
                Select::make('customer_id')
                    ->label('Vásárló')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('order_status_id')
                    ->label('Státusz')
                    ->relationship('orderStatus', 'name')
                    ->default(fn () => OrderStatus::default()?->id)
                    ->required(),
                TextInput::make('shipping_name')
                    ->label('Szállítási név')
                    ->required(),
                TextInput::make('shipping_phone')
                    ->label('Szállítási telefon')
                    ->required(),
                Textarea::make('shipping_address')
                    ->label('Szállítási cím')
                    ->required()
                    ->columnSpanFull(),
                Select::make('payment_method')
                    ->label('Fizetési mód')
                    ->options([
                        'cod' => 'Utánvét',
                        'bank_transfer' => 'Banki átutalás',
                    ])
                    ->required(),
                Repeater::make('items')
                    ->label('Tételek')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Termék')
                            ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                $product = Product::find($state);
                                $lineTotal = ($product?->price ?? 0) * ((int) ($get('quantity') ?: 1));

                                $set('product_name', $product?->name);
                                $set('unit_price', $product?->price);
                                $set('line_total', $lineTotal);

                                static::recalculateOrderTotals($get('../'), $set, '../../');
                            }),
                        Hidden::make('product_name'),
                        TextInput::make('unit_price')
                            ->label('Egységár')
                            ->numeric()
                            ->suffix('Ft')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Mennyiség')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                $set('line_total', ((int) $get('unit_price')) * ((int) ($state ?: 0)));

                                static::recalculateOrderTotals($get('../'), $set, '../../');
                            }),
                        TextInput::make('line_total')
                            ->label('Összesen')
                            ->numeric()
                            ->suffix('Ft')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ])
                    ->columns(4)
                    ->defaultItems(1)
                    ->addActionLabel('Tétel hozzáadása')
                    ->live()
                    ->afterStateUpdated(fn (array $state, Set $set) => static::recalculateOrderTotals($state, $set))
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->label('Részösszeg')
                    ->numeric()
                    ->suffix('Ft')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('total')
                    ->label('Végösszeg')
                    ->numeric()
                    ->suffix('Ft')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
            ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private static function recalculateOrderTotals(array $items, Set $set, string $prefix = ''): void
    {
        $subtotal = collect($items)->sum(fn ($item) => (int) ($item['line_total'] ?? 0));

        $set($prefix.'subtotal', $subtotal);
        $set($prefix.'total', $subtotal);
    }
}
