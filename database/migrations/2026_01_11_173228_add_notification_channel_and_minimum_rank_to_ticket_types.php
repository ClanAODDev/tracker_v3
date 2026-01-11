<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->string('notification_channel')->nullable()->after('boilerplate');
            $table->unsignedTinyInteger('minimum_rank')->nullable()->after('notification_channel');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['notification_channel', 'minimum_rank']);
        });
    }
};
