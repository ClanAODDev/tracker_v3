<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropThreadsAndActiveColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('divisions', 'division_structure')) {
            Schema::table('divisions', function ($table) {
                $table->dropColumn(['division_structure', 'welcome_area']);
            });
        }
    }

    public function down(){}
}
