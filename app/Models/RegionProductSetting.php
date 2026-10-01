<?php

namespace App\Models;

use Database\Factories\RegionProductSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionProductSetting extends Model
{
    /** @use HasFactory<RegionProductSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'region_id',
        'product_id',
        'min_stock',
        'max_stock',
    ];

    protected function casts(): array
    {
        return [
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
