<?php

namespace App\Models;

use Database\Factories\ProductInterestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductInterest extends Model
{
    /** @use HasFactory<ProductInterestFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'product_id', 'score'];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
