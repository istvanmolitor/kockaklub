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
        $sites = [
            ['name' => 'István', 'is_active' => true, 'is_main' => true],
            ['name' => 'András', 'is_active' => true, 'is_main' => false],
        ];

        foreach ($sites as $siteData) {
            Site::query()->updateOrCreate(
                ['name' => $siteData['name']],
                $siteData
            );
        }
    }
}
