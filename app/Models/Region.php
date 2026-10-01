<?php

namespace App\Models;

use Database\Factories\RegionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /** @use HasFactory<RegionFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function stockMovementsAsSource(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'source_region_id');
    }

    public function stockMovementsAsDestination(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'destination_region_id');
    }

    public function regionProductStocks(): HasMany
    {
        return $this->hasMany(RegionProductStock::class);
    }
}
