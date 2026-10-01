<?php

namespace App\Models;

use Database\Factories\OrderStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    /** @use HasFactory<OrderStatusLogFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_status_id',
        'previous_order_status_id',
        'user_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function previousOrderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'previous_order_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
