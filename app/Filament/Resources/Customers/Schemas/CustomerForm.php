<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel(),
                Toggle::make('is_buyer')
                    ->label('Vásárló'),
                Toggle::make('is_seller')
                    ->label('Eladó'),
                Section::make('Szállítási adatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('shipping_name')
                            ->label('Név'),
                        Select::make('shipping_country_id')
                            ->label('Ország')
                            ->relationship('shippingCountry', 'name', fn ($query) => $query->orderBy('sort_order'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('shipping_city')
                            ->label('Város'),
                        TextInput::make('shipping_zip')
                            ->label('Irányítószám'),
                        TextInput::make('shipping_address')
                            ->label('Cím')
                            ->columnSpanFull(),
                    ]),
                Section::make('Számlázási adatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('billing_name')
                            ->label('Név'),
                        Select::make('billing_country_id')
                            ->label('Ország')
                            ->relationship('billingCountry', 'name', fn ($query) => $query->orderBy('sort_order'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('billing_city')
                            ->label('Város'),
                        TextInput::make('billing_zip')
                            ->label('Irányítószám'),
                        TextInput::make('billing_address')
                            ->label('Cím')
                            ->columnSpanFull(),
                        TextInput::make('billing_tax_number')
                            ->label('Adószám'),
                    ]),
            ]);
    }
}
