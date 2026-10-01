<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Név')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                IconColumn::make('is_registered')
                    ->label('Regisztrált')
                    ->boolean()
                    ->state(fn ($record) => $record->user_id !== null),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Rendelések'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('user_id')
                    ->label('Regisztrált')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
