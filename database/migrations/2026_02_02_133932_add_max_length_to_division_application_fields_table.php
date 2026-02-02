<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('division_application_fields', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_length')->nullable()->after('required');
        });
    }

    public function down(): void
    {
        Schema::table('division_application_fields', function (Blueprint $table) {
            $table->dropColumn('max_length');
        });
    }
};
