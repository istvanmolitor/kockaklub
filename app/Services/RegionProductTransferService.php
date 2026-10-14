<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Region;
use App\Models\RegionProductTransfer;
use App\Repositories\StockRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegionProductTransferService
{
    public function __construct(private readonly StockRepository $stock) {}

    /**
     * One row per product stocked in either $sourceRegionId or
     * $destinationRegionId (quantity 0 on the side where it isn't stocked),
     * ordered by product name, for the organize-by-region UI.
     *
     * @return Collection<int, object>
     */
    public function productRowsForRegionPair(int $sourceRegionId, int $destinationRegionId): Collection
    {
        $quantitiesByProductAndRegion = [];

        foreach ($this->stock->quantitiesForRegions([$sourceRegionId, $destinationRegionId]) as $row) {
            $quantitiesByProductAndRegion[$row->product_id][$row->region_id] = (int) $row->quantity;
        }

        if ($quantitiesByProductAndRegion === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($quantitiesByProductAndRegion))
            ->with('defaultImage')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => (object) [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'image_url' => $product->default_image_url,
                'source_quantity' => $quantitiesByProductAndRegion[$product->id][$sourceRegionId] ?? 0,
                'destination_quantity' => $quantitiesByProductAndRegion[$product->id][$destinationRegionId] ?? 0,
            ]);
    }

    /**
     * Moves $quantity of $productId from $source directly into $destination
     * inside the same site: adjusts the region_product_stocks snapshot for
     * both regions and records the move in the region_product_transfers log.
     * Does not create a StockMovement.
     */
    public function transfer(Region $source, Region $destination, int $productId, int $quantity): RegionProductTransfer
    {
        if ($source->site_id !== $destination->site_id) {
            throw ValidationException::withMessages([
                'region' => 'A forrás és a cél régiónak ugyanahhoz a telephelyhez kell tartoznia.',
            ]);
        }

        if ($source->is($destination)) {
            throw ValidationException::withMessages([
                'region' => 'A forrás és a cél régió nem lehet azonos.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'A mennyiségnek nagyobbnak kell lennie, mint 0.',
            ]);
        }

        $available = $this->stock->quantityFor($source->id, $productId);

        if ($quantity > $available) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'A forrás régióban nincs elég készlet (elérhető: %d db, szükséges: %d db).',
                    $available,
                    $quantity
                ),
            ]);
        }

        return DB::transaction(function () use ($source, $destination, $productId, $quantity) {
            $this->stock->adjustStock($source->id, $productId, -$quantity);
            $this->stock->adjustStock($destination->id, $productId, $quantity);

            return RegionProductTransfer::create([
                'source_region_id' => $source->id,
                'destination_region_id' => $destination->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'created_by' => Auth::id(),
            ]);
        });
    }
}
