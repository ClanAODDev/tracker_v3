<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('filament_comments')) {
            return;
        }

        $commentsTable = config('commentions.tables.comments', 'comments');

        DB::table('filament_comments')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($commentsTable) {
                $inserts = $rows->map(fn ($row) => [
                    'author_id' => $row->user_id,
                    'author_type' => 'App\Models\User',
                    'commentable_id' => $row->subject_id,
                    'commentable_type' => $row->subject_type,
                    'body' => $row->comment,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])->all();

                DB::table($commentsTable)->insert($inserts);
            });

        Schema::drop('filament_comments');
    }

    public function down(): void
    {
        Schema::create('filament_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->text('comment');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
