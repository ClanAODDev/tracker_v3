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
        Schema::table('members', function (Blueprint $table) {
            $table->timestamp('last_activity_reminder_at')->nullable()->after('flagged_for_inactivity');
            $table->unsignedInteger('activity_reminded_by_id')->nullable()->after('last_activity_reminder_at');
            $table->foreign('activity_reminded_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['activity_reminded_by_id']);
            $table->dropColumn(['last_activity_reminder_at', 'activity_reminded_by_id']);
        });
    }
};
