<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('division_id')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('depth');
            $table->string('path')->default('/');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('gen_pop')->default(false);
            $table->unsignedMediumInteger('leader_id')->nullable();
            $table->string('legacy_type', 16)->nullable();
            $table->unsignedInteger('legacy_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['legacy_type', 'legacy_id']);
            $table->index('division_id');
            $table->index('parent_id');
            $table->index('path');
            $table->index('leader_id');
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('parent_id')->references('id')->on('units')->nullOnDelete();
        });

        Schema::create('division_unit_levels', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('division_id');
            $table->unsignedTinyInteger('depth');
            $table->string('label');
            $table->string('label_plural');
            $table->string('leader_title');
            $table->timestamps();

            $table->unique(['division_id', 'depth']);
            $table->foreign('division_id')->references('id')->on('divisions')->cascadeOnDelete();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->unsignedInteger('unit_id')->nullable()->after('squad_id');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });

        Schema::dropIfExists('division_unit_levels');
        Schema::dropIfExists('units');
    }
};
