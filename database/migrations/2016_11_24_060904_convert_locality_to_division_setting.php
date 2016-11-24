<?php

use App\Division;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertLocalityToDivisionSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // drop locality column
        if (Schema::hasColumn('divisions', 'locality')) {
            Schema::table('divisions', function ($table) {
                $table->dropColumn(['locality']);
            });
        }

        // populate with default locality

        $divisions = Division::all();

        foreach ($divisions as $division) {
            $settings = $division->settings();

            // provide defaults for locality
            $settings->set('locality', [
                'squad' => 'squad',
                'platoon' => 'platoon',
                'squad leader' => 'squad leader',
                'platoon leader' => 'platoon leader',
            ]);

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
