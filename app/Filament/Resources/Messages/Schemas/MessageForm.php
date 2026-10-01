<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Üzenet')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Név')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('email')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('phone')
                            ->label('Telefon')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('customer_name')
                            ->label('Ügyfél')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->customer?->name),
                        Textarea::make('message')
                            ->label('Üzenet')
                            ->disabled()
                            ->dehydrated(false)
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                Section::make('Válasz')
                    ->schema([
                        Textarea::make('reply')
                            ->label('Válasz')
                            ->rows(6)
                            ->columnSpanFull(),
                        DateTimePicker::make('replied_at')
                            ->label('Megválaszolva')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
