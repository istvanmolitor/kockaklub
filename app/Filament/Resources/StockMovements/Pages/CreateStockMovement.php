<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockMovement;
use App\Services\StockService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->assertStockAvailable($data);

        $data['created_by'] = Auth::id();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertStockAvailable(array $data, ?int $excludingMovementId = null): void
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
                $excludingMovementId,
            );
        }
    }
}
