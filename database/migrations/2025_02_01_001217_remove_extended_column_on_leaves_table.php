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
        if (Schema::hasColumn('leaves', 'extended')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropColumn('extended');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // never come back
    }
};
