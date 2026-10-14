<?php

namespace App\Models;

use Database\Factories\RegionProductTransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegionProductTransfer extends Model
{
    /** @use HasFactory<RegionProductTransferFactory> */
    use HasFactory;

    protected $fillable = [
        'source_region_id',
        'destination_region_id',
        'product_id',
        'quantity',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function sourceRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'source_region_id');
    }

    public function destinationRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'destination_region_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
