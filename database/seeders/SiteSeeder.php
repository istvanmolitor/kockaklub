<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Site::query()->updateOrCreate(
            ['name' => 'Kockaklub'],
            [
                'country' => 'Magyarország',
                'city' => 'Budapest',
                'zip' => '1111',
                'address' => 'Példa utca 1.',
                'is_active' => true,
                'is_main' => true,
            ]
        );
    }
}
