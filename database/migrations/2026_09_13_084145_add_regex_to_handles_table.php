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
        Schema::table('handles', function (Blueprint $table) {
            $table->string('regex')->nullable()->after('type');
            $table->string('regex_hint')->nullable()->after('regex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('handles', function (Blueprint $table) {
            $table->dropColumn(['regex', 'regex_hint']);
        });
    }
};
