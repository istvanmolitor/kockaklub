<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostalCode extends Model
{
    protected $fillable = [
        'country_id',
        'code',
        'city',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
