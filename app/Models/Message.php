<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone',
        'message',
        'reply',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }

    public function isReplied(): bool
    {
        return $this->replied_at !== null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
