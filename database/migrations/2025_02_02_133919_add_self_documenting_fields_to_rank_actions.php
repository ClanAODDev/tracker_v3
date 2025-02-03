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
        Schema::table('rank_actions', function (Blueprint $table) {
            $table->string('justification')->nullable();
            $table->integer('requester_id')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('declined_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rank_actions', function (Blueprint $table) {
            //
        });
    }
};
