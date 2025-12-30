<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->string('tiered_group_name')->nullable()->after('prerequisite_award_id');
            $table->text('tiered_group_description')->nullable()->after('tiered_group_name');
        });

        DB::table('awards')->where('id', 18)->update([
            'tiered_group_name' => 'AOD Tenure',
            'tiered_group_description' => 'Recognition for years of dedicated service to the Angels of Death clan. Each tier represents a milestone in your AOD journey.',
        ]);
    }

    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropColumn(['tiered_group_name', 'tiered_group_description']);
        });
    }
};
