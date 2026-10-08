<?php

namespace App\Models;

use Database\Factories\DeliveryPointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryPoint extends Model
{
    /** @use HasFactory<DeliveryPointFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'reference_id',
        'name',
        'zip',
        'city',
        'address',
        'lat',
        'lng',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'raw_payload' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
