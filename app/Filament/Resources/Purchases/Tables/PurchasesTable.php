<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Purchase;
use App\Models\PurchaseStatus;
use App\Services\PurchaseService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('note')
                    ->label('Megjegyzés')
                    ->limit(40)
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('ordered_at')
                    ->label('Megrendelve')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Eladó')
                    ->searchable(),
                TextColumn::make('destinationSite.name')
                    ->label('Telephely'),
                TextColumn::make('currency.code')
                    ->label('Deviza'),
                TextColumn::make('total_foreign_amount')
                    ->label('Végösszeg (deviza)')
                    ->state(fn (Purchase $record) => number_format($record->totalForeignAmount(), 2).' '.$record->currency->code),
                TextColumn::make('paid_amount_default_currency')
                    ->label('Kifizetve (Ft)')
                    ->money('HUF', decimalPlaces: 0)
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('purchaseStatus.name')
                    ->label('Állapot')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? (PurchaseStatus::labels()[$state] ?? $state) : null)
                    ->color(fn (?string $state) => match ($state) {
                        PurchaseStatus::ORDERED => 'info',
                        PurchaseStatus::RECEIVED => 'success',
                        PurchaseStatus::CANCELLED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('url')
                    ->label('Link')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->formatStateUsing(fn (?string $state) => filled($state) ? 'Megrendelés' : null)
                    ->url(fn (Purchase $record) => $record->url)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Létrehozva')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('purchase_status_id')
                    ->label('Állapot')
                    ->relationship('purchaseStatus', 'name')
                    ->getOptionLabelFromRecordUsing(fn (PurchaseStatus $record) => $record->label()),
            ])
            ->recordActions([
                Action::make('receive')
                    ->label('Beérkeztetés')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (Purchase $record) => $record->purchaseStatus?->name === PurchaseStatus::ORDERED)
                    ->requiresConfirmation()
                    ->modalDescription('A beérkeztetés létrehoz egy készletre rakás mozgatást, és a mozgatás szerkesztő oldalára navigál, ahol kiválasztható a célrégió.')
                    ->action(function (Purchase $record) {
                        try {
                            app(PurchaseService::class)->receive($record);

                            Notification::make()
                                ->title('A beszerzés beérkeztetve')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('A beérkeztetés sikertelen')
                                ->body(implode(' ', $exception->validator->errors()->all()))
                                ->danger()
                                ->send();
                        }
                    })
                    ->successRedirectUrl(fn (Purchase $record) => $record->stock_movement_id
                        ? StockMovementResource::getUrl('edit', ['record' => $record->stock_movement_id])
                        : null),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
