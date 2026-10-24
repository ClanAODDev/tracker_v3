<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('monitored_scheduled_tasks')->where('name', 'tracker:notify-milestone-awards')->delete();
    }

    public function down(): void {}
};
