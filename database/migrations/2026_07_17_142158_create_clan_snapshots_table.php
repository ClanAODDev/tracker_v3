<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clan_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('total_members');
            $table->unsignedSmallInteger('active_divisions');
            $table->unsignedInteger('weekly_active_count');
            $table->unsignedInteger('weekly_voice_count');
            $table->unsignedInteger('monthly_recruits');
            $table->decimal('voice_participation', 5, 2);
            $table->date('snapshot_date')->unique();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clan_snapshots');
    }
};
