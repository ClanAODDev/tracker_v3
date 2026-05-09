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
        Schema::table('division_applications', function (Blueprint $table) {
            $table->string('discord_avatar')->nullable()->after('division_id');
        });
    }

    public function down(): void
    {
        Schema::table('division_applications', function (Blueprint $table) {
            $table->dropColumn('discord_avatar');
        });
    }
};
