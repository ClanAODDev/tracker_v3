<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('division_member_fields', function (Blueprint $table) {
            $table->boolean('self_editable')->default(false)->after('filterable');
        });
    }

    public function down(): void
    {
        Schema::table('division_member_fields', function (Blueprint $table) {
            $table->dropColumn('self_editable');
        });
    }
};
