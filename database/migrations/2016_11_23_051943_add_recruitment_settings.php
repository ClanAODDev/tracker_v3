<?php

use App\Division;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecruitmentSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $divisions = Division::all();

        foreach ($divisions as $division) {
            $settings = $division->settings();

            $settings->set('division_structure', '');
            $settings->set('welcome_area', '');
            $settings->set('use_welcome_thread', false);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
