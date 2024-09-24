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
        if (Schema::hasColumn('rank_actions', 'rank_id')) {
            Schema::table('rank_actions', function (Blueprint $table) {
                $table->renameColumn(from: 'rank_id', to: 'rank');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('rank_actions', 'rank')) {
            Schema::table('rank_actions', function (Blueprint $table) {
                $table->renameColumn(from: 'rank', to: 'rank_id');
            });
        }
    }
};
