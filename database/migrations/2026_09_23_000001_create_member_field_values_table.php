<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('member_id');
            $table->unsignedBigInteger('division_member_field_id');
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('division_member_field_id', 'member_field_values_field_id_foreign')
                ->references('id')->on('division_member_fields')->cascadeOnDelete();
            $table->unique(['member_id', 'division_member_field_id'], 'member_field_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_field_values');
    }
};
