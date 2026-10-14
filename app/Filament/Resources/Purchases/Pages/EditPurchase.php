<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\Purchase;
use App\Models\PurchaseStatus;
use App\Services\PurchaseService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
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
            DeleteAction::make(),
        ];
    }
}
