<?php

namespace App\Models;

use App\Observers\OrderStatusObserver;
use Database\Factories\OrderStatusFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(OrderStatusObserver::class)]
class OrderStatus extends Model
{
    /** @use HasFactory<OrderStatusFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'sort_order',
        'is_default',
        'is_final',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_default' => 'boolean',
            'is_final' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
