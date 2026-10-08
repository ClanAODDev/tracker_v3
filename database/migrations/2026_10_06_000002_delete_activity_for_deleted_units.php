<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['App\Models\Platoon' => 'platoons', 'App\Models\Squad' => 'squads'] as $type => $table) {
            DB::table('activities')
                ->where('subject_type', $type)
                ->whereNotExists(fn ($query) => $query->from($table)->whereColumn("{$table}.id", 'activities.subject_id'))
                ->delete();
        }
    }

    public function down(): void {}
};
