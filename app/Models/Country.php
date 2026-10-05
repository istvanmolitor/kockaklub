<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sort_order',
    ];

    public function postalCodes(): HasMany
    {
        return $this->hasMany(PostalCode::class);
    }
}
