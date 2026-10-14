<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $attributes = [
        'is_buyer' => false,
        'is_seller' => false,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'shipping_name',
        'shipping_country_id',
        'shipping_city',
        'shipping_zip',
        'shipping_address',
        'billing_name',
        'billing_country_id',
        'billing_city',
        'billing_zip',
        'billing_address',
        'billing_tax_number',
        'default_shipping_method_id',
        'default_payment_method_id',
        'is_buyer',
        'is_seller',
    ];

    protected function casts(): array
    {
        return [
            'is_buyer' => 'boolean',
            'is_seller' => 'boolean',
        ];
    }

    public function hasShippingDetails(): bool
    {
        return filled($this->shipping_name) && filled($this->shipping_address);
    }

    public function hasBillingDetails(): bool
    {
        return filled($this->billing_name) && filled($this->billing_address);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'shipping_country_id');
    }

    public function billingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'billing_country_id');
    }

    public function defaultShippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function defaultPaymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function sellerPurchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
