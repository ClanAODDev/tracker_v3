<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddProperNameToDivisions
 * Column for handling proper names for situations like "Floater" division
 */
class AddProperNameToDivisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->string('proper_name')
                ->after('name')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('divisions', 'proper_name')) {
            Schema::table('divisions', function ($table) {
                $table->dropColumn('proper_name');
            });
        }
    }
}
