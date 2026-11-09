<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('division_applications', function (Blueprint $table) {
            $table->unsignedInteger('handle_id')->nullable()->after('discord_avatar');
            $table->string('handle_value')->nullable()->after('handle_id');

            $table->foreign('handle_id')->references('id')->on('handles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('division_applications', function (Blueprint $table) {
            $table->dropForeign(['handle_id']);
            $table->dropColumn(['handle_id', 'handle_value']);
        });
    }
};
