<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('ranks');
        Schema::dropIfExists('positions');
    }

    public function down(): void
    {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('ranks', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('abbreviation');
        });

        Schema::create('positions', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('class');
            $table->integer('order');
        });
    }
};
