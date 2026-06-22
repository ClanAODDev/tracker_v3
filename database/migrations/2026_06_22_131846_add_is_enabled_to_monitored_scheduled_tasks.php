<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitored_scheduled_tasks', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('grace_time_in_minutes');
        });

        DB::table('monitored_scheduled_tasks')->update(['is_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('monitored_scheduled_tasks', function (Blueprint $table) {
            $table->dropColumn('is_enabled');
        });
    }
};
