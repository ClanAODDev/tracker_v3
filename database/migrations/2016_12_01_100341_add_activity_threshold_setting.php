<?php

use App\Division;
use Illuminate\Database\Migrations\Migration;

class AddActivityThresholdSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $divisions = Division::all();

        $divisions->each(function ($division) {
            $division->settings()->set('activity_threshold', [
                [
                    'days' => 30,
                    'class' => 'text-danger'
                ],
                [
                    'days' => 14,
                    'class' => 'text-warning'
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
