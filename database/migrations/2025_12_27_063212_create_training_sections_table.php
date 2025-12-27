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
        Schema::create('training_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->longText('content');
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_sections');
    }
};
