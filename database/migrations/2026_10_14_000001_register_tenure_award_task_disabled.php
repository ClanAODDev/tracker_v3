<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TASK = 'cleanup-tenure-awards';

    public function up(): void
    {
        if (DB::table('monitored_scheduled_tasks')->where('name', self::TASK)->exists()) {
            return;
        }

        DB::table('monitored_scheduled_tasks')->insert([
            'name'                  => self::TASK,
            'type'                  => 'object',
            'cron_expression'       => '0 17 * * *',
            'timezone'              => 'America/New_York',
            'grace_time_in_minutes' => 5,
            'is_enabled'            => false,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('monitored_scheduled_tasks')->where('name', self::TASK)->delete();
    }
};
