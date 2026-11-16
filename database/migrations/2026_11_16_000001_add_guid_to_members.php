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
        Schema::table('members', function (Blueprint $table) {
            $table->string('guid', 10)->nullable()->after('id');
        });

        $taken = [];

        DB::table('members')->orderBy('id')->select('id')->chunkById(1000, function ($members) use (&$taken) {
            foreach ($members as $member) {
                do {
                    $guid = Str::random(10);
                } while (ctype_digit($guid) || isset($taken[strtolower($guid)]));

                $taken[strtolower($guid)] = true;

                DB::table('members')->where('id', $member->id)->update(['guid' => $guid]);
            }
        });

        Schema::table('members', function (Blueprint $table) {
            $table->string('guid', 10)->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['guid']);
            $table->dropColumn('guid');
        });
    }
};
