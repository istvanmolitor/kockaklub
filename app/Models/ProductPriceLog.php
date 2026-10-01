<?php

namespace App\Models;

use Database\Factories\ProductPriceLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPriceLog extends Model
{
    /** @use HasFactory<ProductPriceLogFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price',
        'previous_price',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'previous_price' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
