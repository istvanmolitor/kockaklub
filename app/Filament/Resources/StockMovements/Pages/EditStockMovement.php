<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(fn () => app(StockService::class)->rebuildRegionProductStocks()),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->assertStockAvailable($data);

        return $data;
    }

    protected function afterSave(): void
    {
        app(StockService::class)->rebuildRegionProductStocks();
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
