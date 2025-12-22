<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->unsignedBigInteger('division_tag_id');
            $table->unsignedInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('member_id')
                ->references('id')
                ->on('members')
                ->cascadeOnDelete();

            $table->foreign('division_tag_id')
                ->references('id')
                ->on('division_tags')
                ->cascadeOnDelete();

            $table->foreign('assigned_by')
                ->references('id')
                ->on('members')
                ->nullOnDelete();

            $table->unique(['member_id', 'division_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_tag');
    }
};
