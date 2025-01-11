<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['platoons', 'squads'] as $table) {

            // fix leader_id columns
            Schema::table($table, function (Blueprint $table) {
                $table->mediumInteger('leader_id')->nullable()->change();
            });

            // clean up extra 0 values
            \DB::table($table)
                ->where('leader_id', 0)
                ->update(['leader_id' => null]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
