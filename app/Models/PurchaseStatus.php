<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseStatus extends Model
{
    public const ORDERED = 'ordered';

    public const RECEIVED = 'received';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'name',
        'description',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public static function findByName(string $name): self
    {
        return static::where('name', $name)->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ORDERED => 'Megrendelve',
            self::RECEIVED => 'Beérkezett',
            self::CANCELLED => 'Sztornózva',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->name] ?? $this->name;
    }
}
