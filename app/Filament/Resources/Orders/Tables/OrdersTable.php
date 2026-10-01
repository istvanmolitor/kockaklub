<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\SzamlazzService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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
                TextColumn::make('site.name')
                    ->label('Telephely')
                    ->searchable(),
                TextColumn::make('total')
                    ->label('Összeg')
                    ->money('HUF', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('orderStatus.name')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn ($record) => $record->orderStatus->color),
                TextColumn::make('invoice_number')
                    ->label('Számla')
                    ->badge()
                    ->state(fn (Order $record) => $record->isInvoiced() ? $record->invoice_number : 'Nincs számlázva')
                    ->color(fn (Order $record) => $record->isInvoiced() ? 'success' : 'gray'),
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
                SelectFilter::make('site_id')
                    ->label('Telephely')
                    ->relationship('site', 'name')
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
                Action::make('issueInvoice')
                    ->label('Számla kiállítása')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->visible(fn (Order $record) => ! $record->isInvoiced())
                    ->requiresConfirmation()
                    ->modalDescription('Biztosan kiállítod a számlát a Számlázz.hu-n keresztül? A művelet nem vonható vissza az adminból.')
                    ->action(function (Order $record, SzamlazzService $szamlazzService) {
                        try {
                            $szamlazzService->issueInvoice($record);

                            Notification::make()
                                ->title('Számla kiállítva')
                                ->body($record->fresh()->invoice_number)
                                ->success()
                                ->send();
                        } catch (\Throwable $exception) {
                            Notification::make()
                                ->title('A számla kiállítása sikertelen')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('downloadInvoice')
                    ->label('Számla letöltése')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn (Order $record) => $record->isInvoiced() && filled($record->invoice_pdf_path))
                    ->action(fn (Order $record) => Storage::disk('local')->download($record->invoice_pdf_path, $record->invoice_number.'.pdf')),
                EditAction::make(),
            ]);
    }
}
