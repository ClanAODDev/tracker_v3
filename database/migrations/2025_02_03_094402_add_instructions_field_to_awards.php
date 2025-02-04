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
        if (! Schema::hasColumn('awards', 'instructions')) {
            Schema::table('awards', function (Blueprint $table) {
                $table->string('instructions')->nullable();
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no going back
    }
};
