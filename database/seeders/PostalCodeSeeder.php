<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostalCodeSeeder extends Seeder
{
    /**
     * Source: Magyar Posta + KSH (Hungarian Central Statistics Office) settlement
     * registry, one city per postal code (largest-population settlement wins
     * where a code is shared by multiple small villages).
     */
    public function run(): void
    {
        $hungary = Country::where('code', 'HU')->first();

        if (! $hungary) {
            return;
        }

        $path = __DIR__.'/data/hu-postal-codes.json';
        $codes = json_decode(file_get_contents($path), true);

        $now = now();

        $rows = [];
        foreach ($codes as $code => $city) {
            $rows[] = [
                'country_id' => $hungary->id,
                'code' => $code,
                'city' => $city,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('postal_codes')->upsert($chunk, ['country_id', 'code'], ['city', 'updated_at']);
        }
    }
}
