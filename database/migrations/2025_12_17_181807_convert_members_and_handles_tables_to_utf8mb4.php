<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE members CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        DB::statement('ALTER TABLE handles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE members CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci');
        DB::statement('ALTER TABLE handles CONVERT TO CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci');
    }
};
