<?php

namespace App\Models;

use Database\Factories\RegionProductStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionProductStock extends Model
{
    /** @use HasFactory<RegionProductStockFactory> */
    use HasFactory;

    protected $fillable = [
        'region_id',
        'product_id',
        'quantity',
        'min_stock',
        'max_stock',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
