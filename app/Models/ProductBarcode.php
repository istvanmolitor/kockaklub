<?php

namespace App\Models;

use Database\Factories\ProductBarcodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBarcode extends Model
{
    /** @use HasFactory<ProductBarcodeFactory> */
    use HasFactory;

    protected $fillable = [
        'barcode',
    ];

    public function productPivots(): HasMany
    {
        return $this->hasMany(BarcodeProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'barcode_product')
            ->using(BarcodeProduct::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
