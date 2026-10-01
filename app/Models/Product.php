<?php

namespace App\Models;

use App\Services\StockService;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    public const SORT_OPTIONS = [
        'newest' => 'Legújabb',
        'price_asc' => 'Ár szerint növekvő',
        'price_desc' => 'Ár szerint csökkenő',
        'name_asc' => 'Név szerint (A-Z)',
        'name_desc' => 'Név szerint (Z-A)',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sku',
        'is_active',
        'is_discontinued',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_active' => 'boolean',
            'is_discontinued' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function defaultImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'attribute_value_product');
    }

    public function stockMovementItems(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function regionStocks(): HasMany
    {
        return $this->hasMany(RegionProductStock::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(ProductInterest::class);
    }

    protected function defaultImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->defaultImage?->url() ?? asset('images/product-placeholder.svg'));
    }

    /**
     * Whether the product can still be ordered given its current public stock.
     * Non-discontinued products stay orderable (backorder) even out of stock;
     * discontinued products stop being orderable once their stock reaches zero.
     */
    public function isOrderable(int $publicStock): bool
    {
        return ! $this->is_discontinued || $publicStock > 0;
    }

    /**
     * Adds a `public_stock` column to the query: the aggregated quantity of this
     * product across all public regions, derived from stock movement history.
     */
    public function scopeWithPublicStock(Builder $query): Builder
    {
        return $query->addSelect(['public_stock' => StockService::publicStockSubquery()]);
    }

    /**
     * Orders the query by one of self::SORT_OPTIONS, defaulting to newest first.
     */
    public function scopeSortBy(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            default => $query->latest(),
        };
    }
}
