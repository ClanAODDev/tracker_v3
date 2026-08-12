<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fallen_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->unsignedInteger('member_id')->nullable();
            $table->date('date_fallen')->nullable();
            $table->string('forum_profile')->nullable();
            $table->integer('display_order')->default(100);
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fallen_members');
    }
};
