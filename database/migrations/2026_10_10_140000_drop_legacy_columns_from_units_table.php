<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['legacy_type', 'legacy_id']);
            $table->dropColumn(['legacy_type', 'legacy_id']);
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('legacy_type', 16)->nullable();
            $table->unsignedInteger('legacy_id')->nullable();
            $table->unique(['legacy_type', 'legacy_id']);
        });
    }
};
