<?php

namespace App\Models;

use App\Enums\ShippingFulfillmentType;
use App\Observers\ShippingMethodObserver;
use Database\Factories\ShippingMethodFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(ShippingMethodObserver::class)]
class ShippingMethod extends Model
{
    /** @use HasFactory<ShippingMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost',
        'is_active',
        'is_default',
        'fulfillment_type',
        'locker_provider',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'fulfillment_type' => ShippingFulfillmentType::class,
        ];
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
