<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_status_id',
        'order_number',
        'shipping_name',
        'shipping_phone',
        'shipping_country',
        'shipping_city',
        'shipping_zip',
        'shipping_address',
        'billing_name',
        'billing_country',
        'billing_city',
        'billing_zip',
        'billing_address',
        'billing_tax_number',
        'shipping_method_id',
        'payment_method_id',
        'subtotal',
        'shipping_cost',
        'payment_cost',
        'total',
        'invoice_number',
        'invoiced_at',
        'invoice_pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'payment_cost' => 'integer',
            'total' => 'integer',
            'invoiced_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }

    public function isInvoiced(): bool
    {
        return filled($this->invoice_number);
    }
}
