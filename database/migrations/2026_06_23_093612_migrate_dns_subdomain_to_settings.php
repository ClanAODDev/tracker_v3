<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('divisions')
            ->whereColumn('dns_subdomain', '!=', 'slug')
            ->whereNotNull('dns_subdomain')
            ->get()
            ->each(function ($division) {
                $settings                  = json_decode($division->settings ?? '{}', true) ?? [];
                $settings['dns_subdomain'] = $division->dns_subdomain;
                DB::table('divisions')->where('id', $division->id)->update([
                    'settings' => json_encode($settings),
                ]);
            });

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('dns_subdomain');
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('dns_subdomain')->nullable()->after('slug');
        });

        DB::table('divisions')->get()->each(function ($division) {
            $settings = json_decode($division->settings ?? '{}', true) ?? [];
            DB::table('divisions')->where('id', $division->id)->update([
                'dns_subdomain' => $settings['dns_subdomain'] ?? $division->slug,
            ]);
        });
    }
};
