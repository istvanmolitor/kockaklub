<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('close')
                ->label('Lezárás')
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->visible(fn (StockMovement $record) => ! $record->isClosed())
                ->requiresConfirmation()
                ->modalDescription('Lezárás után a mozgatás módosítja a készletet, és a tételek már nem szerkeszthetők.')
                ->action(function (StockMovement $record) {
                    try {
                        app(StockService::class)->closeMovement($record);

                        Notification::make()
                            ->title('A készlet mozgatás lezárva')
                            ->success()
                            ->send();
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('A lezárás sikertelen')
                            ->body(implode(' ', $exception->validator->errors()->all()))
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make()
                ->after(fn () => app(StockService::class)->rebuildRegionProductStocks()),
        ];
    }

    protected function getFormActions(): array
    {
        if ($this->record->isClosed()) {
            return [
                $this->getCancelFormAction(),
            ];
        }

        return parent::getFormActions();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertStockAvailable($data);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertStockAvailable(array $data): void
    {
        if (! in_array($data['type'], [StockMovement::TYPE_OUT, StockMovement::TYPE_TRANSFER], true)) {
            return;
        }

        $quantitiesByProduct = collect($data['items'] ?? [])
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum('quantity'));

        foreach ($quantitiesByProduct as $productId => $quantity) {
            app(StockService::class)->assertCanRemove(
                (int) $productId,
                (int) $data['source_region_id'],
                (int) $quantity,
                $this->record->id,
            );
        }
    }
}
