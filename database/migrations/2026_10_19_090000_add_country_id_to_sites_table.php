<?php

use App\Models\Country;
use App\Models\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('country')->constrained('countries')->nullOnDelete();
        });

        Site::query()->whereNotNull('country')->each(function (Site $site) {
            $countryId = Country::where('name', $site->country)->value('id');

            if ($countryId) {
                $site->forceFill(['country_id' => $countryId])->save();
            }
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('country')->nullable()->after('country_id');
        });

        Site::query()->whereNotNull('country_id')->with('country')->each(function (Site $site) {
            $site->forceFill(['country' => $site->country?->name])->save();
        });

        Schema::table('sites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
