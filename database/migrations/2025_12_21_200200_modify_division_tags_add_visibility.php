<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('division_tags', function (Blueprint $table) {
            $table->dropColumn('color');
            $table->string('visibility', 20)->default('public')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('division_tags', function (Blueprint $table) {
            $table->dropColumn('visibility');
            $table->string('color', 7)->nullable()->after('name');
        });
    }
};
