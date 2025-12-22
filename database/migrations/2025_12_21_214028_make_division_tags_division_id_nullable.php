<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('division_tags', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropUnique(['division_id', 'name']);
        });

        Schema::table('division_tags', function (Blueprint $table) {
            $table->unsignedInteger('division_id')->nullable()->change();

            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->nullOnDelete();

            $table->unique(['division_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('division_tags', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropUnique(['division_id', 'name']);
        });

        Schema::table('division_tags', function (Blueprint $table) {
            $table->unsignedInteger('division_id')->nullable(false)->change();

            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->cascadeOnDelete();

            $table->unique(['division_id', 'name']);
        });
    }
};
