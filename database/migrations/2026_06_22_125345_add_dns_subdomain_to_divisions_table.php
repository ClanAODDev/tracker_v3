<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('dns_subdomain')->nullable()->after('slug');
        });

        DB::table('divisions')->get()->each(function ($division) {
            DB::table('divisions')->where('id', $division->id)->update([
                'dns_subdomain' => $division->slug,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('dns_subdomain');
        });
    }
};
