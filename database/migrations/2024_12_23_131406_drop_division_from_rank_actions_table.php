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
        if (Schema::hasColumn('rank_actions', 'division_id')) {
            Schema::table('rank_actions', function (Blueprint $table) {
                $table->dropColumn('division_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no coming back!
    }
};
