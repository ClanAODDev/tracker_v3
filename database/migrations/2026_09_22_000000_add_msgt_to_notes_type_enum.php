<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE notes MODIFY type ENUM('positive','negative','misc','sr_ldr','msgt') NOT NULL");
    }

    public function down(): void
    {
        DB::table('notes')->where('type', 'msgt')->update(['type' => 'misc']);

        DB::statement("ALTER TABLE notes MODIFY type ENUM('positive','negative','misc','sr_ldr') NOT NULL");
    }
};
