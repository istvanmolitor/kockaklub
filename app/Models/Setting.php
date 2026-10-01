<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings'));
        static::deleted(fn () => Cache::forget('settings'));
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::cachedValues()[$key] ?? $default;
    }

    /**
     * @return array<string, string|null>
     */
    protected static function cachedValues(): array
    {
        return Cache::rememberForever(
            'settings',
            fn () => static::query()->pluck('value', 'key')->all()
        );
    }

    public static function set(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
