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
        Schema::table('member_requests', function (Blueprint $table) {
            $table->dropColumn('processed_at', 'cancelled_at');
            $table->renameColumn('canceller_id', 'holder_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no coming back!
    }
};
