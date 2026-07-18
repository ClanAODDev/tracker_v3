<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('division_id');
            $table->string('category', 20);
            $table->unsignedTinyInteger('rank');
            $table->decimal('value', 8, 2);
            $table->unsignedTinyInteger('previous_rank')->nullable();
            $table->tinyInteger('rank_change')->default(0);
            $table->json('trend_data')->nullable();
            $table->date('snapshot_date');
            $table->timestamp('created_at')->nullable();

            $table->index(['division_id', 'category', 'snapshot_date']);
            $table->index(['category', 'snapshot_date', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
    }
};
