<?php

namespace App\Models;

use App\Observers\PurchaseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(PurchaseObserver::class)]
class Purchase extends Model
{
    protected $fillable = [
        'customer_id',
        'destination_site_id',
        'currency_id',
        'url',
        'ordered_at',
        'expected_arrival_date_min',
        'expected_arrival_date_max',
        'received_at',
        'received_by',
        'stock_movement_id',
        'purchase_status_id',
        'paid_amount_default_currency',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'expected_arrival_date_min' => 'date',
            'expected_arrival_date_max' => 'date',
            'received_at' => 'datetime',
            'paid_amount_default_currency' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function destinationSite(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'destination_site_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function purchaseStatus(): BelongsTo
    {
        return $this->belongsTo(PurchaseStatus::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(PurchaseCost::class);
    }

    public function isReceived(): bool
    {
        return $this->purchaseStatus?->name === PurchaseStatus::RECEIVED;
    }

    public function totalForeignAmount(): string|float
    {
        return $this->items->sum(fn (PurchaseItem $item) => $item->lineTotal());
    }

    /**
     * Recomputes and persists purchase_items.unit_cost_default_currency for
     * every sibling item: the paid_amount_default_currency total allocated
     * across items in proportion to their foreign-currency line total, with
     * the rounding remainder absorbed by the last item (by id) so the
     * allocated totals always sum exactly to paid_amount_default_currency.
     */
    public function recalculateItemUnitCosts(): void
    {
        $items = $this->items()->orderBy('id')->get();

        if ($items->isEmpty()) {
            return;
        }

        $totalForeignValue = $items->sum(fn (PurchaseItem $item) => $item->lineTotal());

        if ($this->paid_amount_default_currency === null || $totalForeignValue <= 0) {
            $items->each(function (PurchaseItem $item) {
                $item->unit_cost_default_currency = null;
                $item->saveQuietly();
            });

            return;
        }

        $allocatedTotalSoFar = 0;
        $lastItem = $items->last();

        foreach ($items as $item) {
            if ($item->is($lastItem)) {
                $allocatedTotal = $this->paid_amount_default_currency - $allocatedTotalSoFar;
            } else {
                $allocatedTotal = (int) round($this->paid_amount_default_currency * ($item->lineTotal() / $totalForeignValue));
                $allocatedTotalSoFar += $allocatedTotal;
            }

            $item->unit_cost_default_currency = (int) round($allocatedTotal / $item->quantity);
            $item->saveQuietly();
        }
    }
}
