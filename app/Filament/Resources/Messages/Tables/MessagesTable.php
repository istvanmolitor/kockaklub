<?php

namespace App\Filament\Resources\Messages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
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
                TextColumn::make('message')
                    ->label('Üzenet')
                    ->limit(60)
                    ->wrap(),
                IconColumn::make('replied_at')
                    ->label('Megválaszolva')
                    ->boolean()
                    ->state(fn ($record) => $record->isReplied()),
                TextColumn::make('created_at')
                    ->label('Beérkezett')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('replied_at')
                    ->label('Megválaszolva')
                    ->nullable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
