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
        if (Schema::hasColumn('members', 'rank_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->renameColumn(from: 'rank_id', to: 'rank');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('members', 'rank')) {
            Schema::table('members', function (Blueprint $table) {
                $table->renameColumn(from: 'rank', to: 'rank_id');
            });
        }
    }
};
