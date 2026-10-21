<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Enums\PaymentMethodType;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_active')
                    ->label('Aktív')
                    ->default(true)
                    ->required(),
                Toggle::make('is_default')
                    ->label('Alapértelmezett'),
                TextInput::make('name')
                    ->label('Név')
                    ->required(),
                RichEditor::make('description')
                    ->label('Leírás')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->label('Költség')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->suffix('Ft'),
                Select::make('type')
                    ->label('Típus')
                    ->options(array_combine(
                        array_map(fn (PaymentMethodType $case) => $case->value, PaymentMethodType::cases()),
                        array_map(fn (PaymentMethodType $case) => $case->label(), PaymentMethodType::cases()),
                    ))
                    ->default(PaymentMethodType::Cod->value)
                    ->live()
                    ->required(),
                Select::make('shippingMethods')
                    ->label('Szállítási módok')
                    ->relationship('shippingMethods', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            ]);
    }
}
