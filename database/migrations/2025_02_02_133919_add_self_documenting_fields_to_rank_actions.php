<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rank_actions', function (Blueprint $table) {
            $table->text('justification')->nullable();
            $table->integer('requester_id')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('declined_at')->nullable();
        });

        DB::table('rank_actions')->update([
            'approved_at' => DB::raw('created_at'),
            'accepted_at' => DB::raw('created_at'),
        ]);

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
