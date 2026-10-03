<?php

namespace Database\Seeders;

use App\Models\Region;
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
            [
                'name' => 'Kockaklub Budapest',
                'country' => 'Magyarország',
                'city' => 'Budapest',
                'zip' => '1111',
                'address' => 'Példa utca 1.',
                'is_active' => true,
                'is_main' => true,
            ],
            [
                'name' => 'Kockaklub Debrecen',
                'country' => 'Magyarország',
                'city' => 'Debrecen',
                'zip' => '4024',
                'address' => 'Piac utca 20.',
                'is_active' => true,
                'is_main' => false,
            ],
            [
                'name' => 'Kockaklub Szeged',
                'country' => 'Magyarország',
                'city' => 'Szeged',
                'zip' => '6720',
                'address' => 'Kárász utca 10.',
                'is_active' => true,
                'is_main' => false,
            ],
        ];

        $regions = [
            ['name' => 'Eladótér', 'is_public' => true],
            ['name' => 'Raktár', 'is_public' => false],
            ['name' => 'Sérült áru', 'is_public' => false],
        ];

        foreach ($sites as $siteData) {
            $site = Site::query()->updateOrCreate(
                ['name' => $siteData['name']],
                $siteData
            );

            foreach ($regions as $regionData) {
                Region::query()->updateOrCreate(
                    ['site_id' => $site->id, 'name' => $regionData['name']],
                    $regionData
                );
            }
        }
    }
}
