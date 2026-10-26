<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('release_notes', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('title');
        });

        $taken = [];

        DB::table('release_notes')->orderBy('id')->get(['id', 'title'])->each(function ($note) use (&$taken) {
            $base = Str::slug($note->title) ?: 'release-note';
            $slug = $base;

            for ($suffix = 2; in_array($slug, $taken, true); $suffix++) {
                $slug = "{$base}-{$suffix}";
            }

            $taken[] = $slug;

            DB::table('release_notes')->where('id', $note->id)->update(['slug' => $slug]);
        });
    }

    public function down(): void
    {
        Schema::table('release_notes', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
