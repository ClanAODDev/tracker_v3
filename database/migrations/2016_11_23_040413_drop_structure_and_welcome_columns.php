<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropStructureAndWelcomeColumns extends Migration
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

    public function down()
    {
    }
}
