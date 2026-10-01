<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
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
                Section::make('Szállítási adatok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('shipping_name')
                            ->label('Név'),
                        TextInput::make('shipping_country')
                            ->label('Ország'),
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
                        TextInput::make('billing_country')
                            ->label('Ország'),
                        TextInput::make('billing_city')
                            ->label('Város'),
                        TextInput::make('billing_zip')
                            ->label('Irányítószám'),
                        TextInput::make('billing_address')
                            ->label('Cím')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
