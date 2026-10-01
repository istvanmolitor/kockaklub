<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'statusLogs';

    protected static ?string $title = 'Státusz előzmények';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('previousOrderStatus.name')
                    ->label('Előző státusz')
                    ->badge()
                    ->color(fn ($record) => $record->previousOrderStatus?->color)
                    ->placeholder('—'),
                TextColumn::make('orderStatus.name')
                    ->label('Új státusz')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus->color),
                TextColumn::make('user.name')
                    ->label('Módosította')
                    ->placeholder('Rendszer'),
                TextColumn::make('created_at')
                    ->label('Dátum')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([]);
    }
}
