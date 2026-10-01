<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Rendelésszám')
                    ->disabled(),
                Select::make('customer_id')
                    ->label('Vásárló')
                    ->relationship('customer', 'name')
                    ->disabled(),
                Select::make('order_status_id')
                    ->label('Státusz')
                    ->relationship('orderStatus', 'name')
                    ->required(),
                TextInput::make('shipping_name')
                    ->label('Szállítási név')
                    ->disabled(),
                TextInput::make('shipping_phone')
                    ->label('Szállítási telefon')
                    ->disabled(),
                Textarea::make('shipping_address')
                    ->label('Szállítási cím')
                    ->disabled()
                    ->columnSpanFull(),
                Select::make('payment_method')
                    ->label('Fizetési mód')
                    ->options([
                        'cod' => 'Utánvét',
                        'bank_transfer' => 'Banki átutalás',
                    ])
                    ->disabled(),
                TextInput::make('subtotal')
                    ->label('Részösszeg')
                    ->numeric()
                    ->suffix('Ft')
                    ->disabled(),
                TextInput::make('total')
                    ->label('Végösszeg')
                    ->numeric()
                    ->suffix('Ft')
                    ->disabled(),
            ]);
    }
}
