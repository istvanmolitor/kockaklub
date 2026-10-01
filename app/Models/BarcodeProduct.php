<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BarcodeProduct extends Pivot
{
    protected $table = 'barcode_product';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'product_barcode_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productBarcode(): BelongsTo
    {
        return $this->belongsTo(ProductBarcode::class);
    }
}
