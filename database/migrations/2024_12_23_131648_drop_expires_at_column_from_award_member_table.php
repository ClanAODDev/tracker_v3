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
        Schema::table('award_member', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no coming back
    }
};
