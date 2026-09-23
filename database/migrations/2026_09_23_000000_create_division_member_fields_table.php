<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('division_member_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('division_id');
            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->json('options')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('filterable')->default(true);
            $table->timestamps();

            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->unique(['division_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('division_member_fields');
    }
};
