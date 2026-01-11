<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $validTypeIds = DB::table('ticket_types')->pluck('id');

        $deleted = DB::table('tickets')
            ->whereNotIn('ticket_type_id', $validTypeIds)
            ->delete();

        if ($deleted > 0) {
            logger()->info("Cleaned up {$deleted} orphaned tickets");
        }
    }

    public function down(): void {}
};
