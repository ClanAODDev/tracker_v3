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
        Schema::create('division_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('division_id');
            $table->string('name', 50);
            $table->string('color', 7)->nullable();
            $table->timestamps();

            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->cascadeOnDelete();

            $table->unique(['division_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_tags');
    }
};
