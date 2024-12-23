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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('image')->nullable();
            $table->integer('display_order')->default(100);
            $table->integer('division_id')->nullable();
            $table->boolean('active')->default(true)->comment('For awards given during certain periods of time');
            $table->boolean('allow_request')->default(false);
            $table->timestamps();
        });

        Schema::create('award_member', function (Blueprint $table) {
            $table->id();
            $table->integer('award_id');
            $table->integer('member_id');
            $table->boolean('approved')->default(false);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
        Schema::dropIfExists('award_member');
    }
};
