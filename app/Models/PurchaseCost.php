<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseCost extends Model
{
    protected $fillable = [
        'purchase_id',
        'purchase_cost_type_id',
        'amount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseCostType(): BelongsTo
    {
        return $this->belongsTo(PurchaseCostType::class);
    }
}
