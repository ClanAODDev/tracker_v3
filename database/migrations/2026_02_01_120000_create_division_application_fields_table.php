<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_application_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('division_id');
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->string('helper_text')->nullable();
            $table->json('options')->nullable();
            $table->boolean('required')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_application_fields');
    }
};
