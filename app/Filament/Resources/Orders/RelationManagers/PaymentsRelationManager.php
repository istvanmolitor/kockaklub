<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Beérkezett fizetések';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label('Összeg')
                    ->money('HUF', decimalPlaces: 0),
                TextColumn::make('received_at')
                    ->label('Beérkezés dátuma')
                    ->dateTime(),
                TextColumn::make('reference')
                    ->label('Hivatkozás')
                    ->placeholder('—'),
                TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->placeholder('—')
                    ->wrap(),
                TextColumn::make('user.name')
                    ->label('Rögzítette')
                    ->placeholder('Rendszer'),
            ])
            ->defaultSort('received_at', 'desc')
            ->recordActions([]);
    }
}
