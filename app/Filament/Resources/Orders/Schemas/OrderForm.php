<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ShippingMethod;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                Section::make('Szállítási adatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('shipping_name')
                            ->label('Név')
                            ->required(),
                        TextInput::make('shipping_phone')
                            ->label('Telefon')
                            ->required(),
                        TextInput::make('shipping_country')
                            ->label('Ország')
                            ->required(),
                        TextInput::make('shipping_city')
                            ->label('Város')
                            ->required(),
                        TextInput::make('shipping_zip')
                            ->label('Irányítószám')
                            ->required(),
                        TextInput::make('shipping_address')
                            ->label('Cím')
                            ->required(),
                    ]),
                Section::make('Számlázási adatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_name')
                            ->label('Név')
                            ->required(),
                        TextInput::make('billing_country')
                            ->label('Ország')
                            ->required(),
                        TextInput::make('billing_city')
                            ->label('Város')
                            ->required(),
                        TextInput::make('billing_zip')
                            ->label('Irányítószám')
                            ->required(),
                        TextInput::make('billing_address')
                            ->label('Cím')
                            ->required(),
                    ]),
                Select::make('shipping_method_id')
                    ->label('Szállítási mód')
                    ->relationship('shippingMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                        $set('shipping_cost', ShippingMethod::find($state)?->cost ?? 0);

                        static::recalculateOrderTotals($get('items') ?? [], $get, $set);
                    }),
                Select::make('payment_method_id')
                    ->label('Fizetési mód')
                    ->relationship('paymentMethod', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                        $set('payment_cost', PaymentMethod::find($state)?->cost ?? 0);

                        static::recalculateOrderTotals($get('items') ?? [], $get, $set);
                    }),
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

                                static::recalculateOrderTotals($get('../'), $get, $set, '../../');
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

                                static::recalculateOrderTotals($get('../'), $get, $set, '../../');
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
                    ->afterStateUpdated(fn (array $state, Set $set, Get $get) => static::recalculateOrderTotals($state, $get, $set))
                    ->columnSpanFull(),
                TextInput::make('subtotal')
                    ->label('Részösszeg')
                    ->numeric()
                    ->suffix('Ft')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('shipping_cost')
                    ->label('Szállítási költség')
                    ->numeric()
                    ->suffix('Ft')
                    ->default(0)
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('payment_cost')
                    ->label('Fizetési költség')
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
    private static function recalculateOrderTotals(array $items, Get $get, Set $set, string $prefix = ''): void
    {
        $subtotal = collect($items)->sum(fn ($item) => (int) ($item['line_total'] ?? 0));
        $shippingCost = (int) ($get($prefix.'shipping_cost') ?: 0);
        $paymentCost = (int) ($get($prefix.'payment_cost') ?: 0);

        $set($prefix.'subtotal', $subtotal);
        $set($prefix.'total', $subtotal + $shippingCost + $paymentCost);
    }
}
