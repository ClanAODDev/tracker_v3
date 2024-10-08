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
        if (Schema::hasColumn('members', 'position_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->renameColumn(from: 'position_id', to: 'position');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->renameColumn(from: 'position', to: 'position_id');
        });
    }
};
