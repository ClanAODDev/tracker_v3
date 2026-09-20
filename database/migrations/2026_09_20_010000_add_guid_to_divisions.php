<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('guid', 10)->nullable()->after('id');
        });

        $existingGuids = [];

        DB::table('divisions')->orderBy('id')->get(['id'])->each(function ($division) use (&$existingGuids) {
            do {
                $guid = Str::random(10);
            } while (in_array($guid, $existingGuids, true));

            $existingGuids[] = $guid;

            DB::table('divisions')->where('id', $division->id)->update(['guid' => $guid]);
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->string('guid', 10)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('guid');
        });
    }
};
