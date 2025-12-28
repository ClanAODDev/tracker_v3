<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_reminders', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('member_id');
            $table->unsignedInteger('division_id');
            $table->unsignedInteger('reminded_by_id');
            $table->timestamps();

            $table->foreign('member_id')->references('clan_id')->on('members')->cascadeOnDelete();
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
            $table->foreign('reminded_by_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['member_id', 'created_at']);
            $table->index(['division_id', 'created_at']);
            $table->index(['reminded_by_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_reminders');
    }
};
