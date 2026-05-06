<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['ts_unique_id', 'last_ts_activity']);
        });

        Schema::table('censuses', function (Blueprint $table) {
            $table->dropColumn('weekly_ts_count');
        });
    }

};
