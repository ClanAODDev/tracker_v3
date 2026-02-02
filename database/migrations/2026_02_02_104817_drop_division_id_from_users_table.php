<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('division_id')->nullable()->after('member_id');
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
        });
    }
};
