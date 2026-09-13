<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('handles', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('regex_hint');
        });

        $this->backfillEnabledFlag();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('handles', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }

    /**
     * Disable any handle type not required by a genuinely active division.
     * `deleted_at` must be checked explicitly: this uses the query builder
     * rather than the `Division` model, which bypasses its SoftDeletes scope.
     */
    public function backfillEnabledFlag(): void
    {
        $activeHandleIds = DB::table('divisions')
            ->where('active', true)
            ->whereNull('deleted_at')
            ->whereNotNull('handle_id')
            ->pluck('handle_id');

        DB::table('handles')
            ->whereNotIn('id', $activeHandleIds)
            ->update(['enabled' => false]);
    }
};
