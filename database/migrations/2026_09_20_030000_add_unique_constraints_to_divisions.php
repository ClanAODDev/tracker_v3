<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->purgeDuplicates('name');
        $this->purgeDuplicates('slug');

        Schema::table('divisions', function (Blueprint $table) {
            $table->unique('name');
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropUnique(['slug']);
        });
    }

    /**
     * Permanently remove any soft-deleted rows sharing a value with a row
     * that's still around (live or trashed), keeping the live one when
     * present, otherwise the oldest trashed one - so the unique index below
     * doesn't fail on leftover duplicates from before this constraint existed.
     */
    private function purgeDuplicates(string $column): void
    {
        $duplicateValues = DB::table('divisions')
            ->select($column)
            ->groupBy($column)
            ->havingRaw('count(*) > 1')
            ->pluck($column);

        foreach ($duplicateValues as $value) {
            $rows = DB::table('divisions')
                ->where($column, $value)
                ->orderByRaw('deleted_at is null desc')
                ->orderBy('id')
                ->get();

            DB::table('divisions')
                ->whereIn('id', $rows->skip(1)->pluck('id'))
                ->delete();
        }
    }
};
