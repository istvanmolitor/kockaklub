<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Repositories\StockRepository;
use App\Services\StockService;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[ObservedBy(ProductObserver::class)]
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
        'meta_description',
        'price',
        'vat_rate',
        'sku',
        'weight',
        'is_active',
        'is_discontinued',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'vat_rate' => 'integer',
            'weight' => 'decimal:3',
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

    public function barcodePivots(): HasMany
    {
        return $this->hasMany(BarcodeProduct::class);
    }

    public function barcodes(): BelongsToMany
    {
        return $this->belongsToMany(ProductBarcode::class, 'barcode_product')
            ->using(BarcodeProduct::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function defaultImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_default', true);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'attribute_value_product');
    }

    public function relatedProductPivots(): HasMany
    {
        return $this->hasMany(RelatedProduct::class)->orderBy('sort_order');
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->using(RelatedProduct::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('sort_order');
    }

    public function stockMovementItems(): HasMany
    {
        return $this->hasMany(StockMovementItem::class);
    }

    public function regionSettings(): HasMany
    {
        return $this->hasMany(RegionProductSetting::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(ProductInterest::class);
    }

    public function priceLogs(): HasMany
    {
        return $this->hasMany(ProductPriceLog::class)->latest('id');
    }

    protected function defaultImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->defaultImage?->url() ?? asset('images/product-placeholder.svg'));
    }

    /**
     * The text used for the meta description and AI/structured-data summaries:
     * the dedicated meta_description field, falling back to the plain-text
     * description truncated to a search-snippet-friendly length.
     */
    protected function seoDescription(): Attribute
    {
        return Attribute::get(
            fn () => $this->meta_description ?: Str::of((string) $this->description)->stripTags()->squish()->limit(160)->toString()
        );
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
     * How much of this product can still be promised to a new order: the
     * public stock minus what's already sitting on orders that exist but
     * haven't been reserved (picked off the shelf) yet.
     */
    public function freeStock(): int
    {
        return app(StockService::class)->freeStockForProduct($this->id);
    }

    /**
     * Adds a `public_stock` column to the query: the aggregated quantity of this
     * product across all public regions, derived from stock movement history.
     */
    public function scopeWithPublicStock(Builder $query): Builder
    {
        return $query->addSelect(['public_stock' => StockRepository::publicStockSubquery()]);
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
