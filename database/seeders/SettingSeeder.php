<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'contact_phone' => ['label' => 'Telefonszám', 'value' => '+36 1 234 5678'],
            'contact_email' => ['label' => 'E-mail cím', 'value' => 'info@kockaklub.hu'],
            'contact_address' => ['label' => 'Cím', 'value' => '1111 Budapest, Példa utca 1.'],
            'company_name' => ['label' => 'Cégnév', 'value' => 'Kockaklub Kft.'],
            'facebook_url' => ['label' => 'Facebook link', 'value' => null],
            'instagram_url' => ['label' => 'Instagram link', 'value' => null],
        ];

        foreach ($settings as $key => $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $setting['label'], 'value' => $setting['value']]
            );
        }
    }
}
