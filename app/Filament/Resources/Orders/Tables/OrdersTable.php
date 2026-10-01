<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\OrderStatus;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Rendelésszám')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Vásárló')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Összeg')
                    ->money('HUF', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('orderStatus.name')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus->color),
                TextColumn::make('created_at')
                    ->label('Dátum')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('order_status_id')
                    ->label('Státusz')
                    ->options(fn () => OrderStatus::pluck('name', 'id')),
                SelectFilter::make('customer_id')
                    ->label('Vásárló')
                    ->relationship('customer', 'name')
                    ->searchable(),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label('Dátumtól'),
                        DatePicker::make('until')->label('Dátumig'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
