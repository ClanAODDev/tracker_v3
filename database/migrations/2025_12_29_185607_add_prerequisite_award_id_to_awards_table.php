<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->unsignedBigInteger('prerequisite_award_id')->nullable()->after('division_id');
            $table->foreign('prerequisite_award_id')->references('id')->on('awards')->nullOnDelete();
        });

        DB::table('awards')->where('id', 140)->update(['prerequisite_award_id' => 139]);
        DB::table('awards')->where('id', 139)->update(['prerequisite_award_id' => 19]);
        DB::table('awards')->where('id', 19)->update(['prerequisite_award_id' => 18]);
    }

    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropForeign(['prerequisite_award_id']);
            $table->dropColumn('prerequisite_award_id');
        });
    }
};
