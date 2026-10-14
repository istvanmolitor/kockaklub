<?php

namespace App\Models;

use App\Observers\CurrencyObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(CurrencyObserver::class)]
class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
