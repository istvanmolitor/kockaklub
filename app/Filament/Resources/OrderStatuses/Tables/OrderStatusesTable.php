<?php

namespace App\Filament\Resources\OrderStatuses\Tables;

use App\Models\OrderStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('Név')
                    ->badge()
                    ->color(fn (OrderStatus $record) => $record->color)
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Sorrend')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Alapértelmezett')
                    ->boolean(),
                IconColumn::make('is_final')
                    ->label('Lezárt')
                    ->boolean(),
                TextColumn::make('orders_count')
                    ->counts('orders')
                    ->label('Rendelések'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, OrderStatus $record) {
                        if ($record->orders()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Nem törölhető')
                                ->body('Erre a státuszra még hivatkozik legalább egy rendelés.')
                                ->send();

                            $action->halt();
                        }
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
