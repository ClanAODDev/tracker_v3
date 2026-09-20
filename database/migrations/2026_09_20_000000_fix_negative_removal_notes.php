<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('notes')
            ->where('type', 'negative')
            ->where('body', 'like', 'Member removal:%')
            ->update(['type' => 'misc']);
    }

    public function down(): void
    {
        // Irreversible: notes fixed here are indistinguishable from removal notes
        // that were already correctly created as `misc` by a different code path.
    }
};
