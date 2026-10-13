<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use App\Observers\PaymentMethodObserver;
use Database\Factories\PaymentMethodFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy(PaymentMethodObserver::class)]
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cost',
        'is_active',
        'is_default',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'type' => PaymentMethodType::class,
        ];
    }

    public function shippingMethods(): BelongsToMany
    {
        return $this->belongsToMany(ShippingMethod::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
