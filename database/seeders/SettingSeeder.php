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
            'facebook_url' => ['label' => 'Facebook link', 'value' => 'https://www.facebook.com/KockaKlubHu'],
            'instagram_url' => ['label' => 'Instagram link', 'value' => 'https://www.instagram.com/kockaklub'],
            'youtube_url' => ['label' => 'Youtube link', 'value' => 'https://www.youtube.com/@KockafejAndr%C3%A1s'],
            'seo_home_description' => ['label' => 'Főoldal meta leírás (SEO)', 'value' => 'Fedezd fel a Kockaklub széles társasjáték- és kiegészítő-kínálatát. Gyors kiszállítás, folyamatosan frissülő készlet.'],
            'szamlazz_api_key' => ['label' => 'Számlázz.hu Agent kulcs', 'value' => null],
            'szamlazz_bank_name' => ['label' => 'Számlázz.hu – bankszámla neve (opcionális)', 'value' => null],
            'szamlazz_bank_account_number' => ['label' => 'Számlázz.hu – bankszámlaszám (opcionális)', 'value' => null],
        ];

        foreach ($settings as $key => $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $setting['label'], 'value' => $setting['value']]
            );
        }
    }
}
