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
        if (! Schema::hasColumns('rank_actions', ['denied_at', 'deny_reason'])) {
            Schema::table('rank_actions', function (Blueprint $table) {
                $table->dateTime('denied_at')->after('accepted_at')->nullable();
                $table->string('deny_reason')->after('denied_at')->nullable();
            });
        }
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
