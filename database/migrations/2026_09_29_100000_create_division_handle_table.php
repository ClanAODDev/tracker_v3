<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('division_handle')) {
            if (DB::table('division_handle')->exists()) {
                throw new RuntimeException('The legacy division_handle table has rows; migrate them manually before running this migration.');
            }

            Schema::drop('division_handle');
        }

        Schema::create('division_handle', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('handle_id');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['division_id', 'handle_id']);
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('handle_id')->references('id')->on('handles')->cascadeOnDelete();
        });

        DB::table('divisions')
            ->join('handles', 'handles.id', '=', 'divisions.handle_id')
            ->select('divisions.id as division_id', 'handles.id as handle_id')
            ->orderBy('divisions.id')
            ->get()
            ->chunk(500)
            ->each(fn ($rows) => DB::table('division_handle')->insert(
                $rows->map(fn ($row) => [
                    'division_id' => $row->division_id,
                    'handle_id'   => $row->handle_id,
                    'sort_order'  => 0,
                ])->all()
            ));

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('handle_id');
        });
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->unsignedInteger('handle_id')->default(0)->after('name');
        });

        DB::table('division_handle')
            ->orderBy('sort_order')
            ->get()
            ->unique('division_id')
            ->each(fn ($row) => DB::table('divisions')
                ->where('id', $row->division_id)
                ->update(['handle_id' => $row->handle_id]));

        Schema::dropIfExists('division_handle');
    }
};
