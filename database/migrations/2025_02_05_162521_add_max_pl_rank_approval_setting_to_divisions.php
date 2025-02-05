<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     *
     * This method iterates over all divisions and sets the
     * settings->max_platoon_leader_rank value to 4.
     *
     * @return void
     */
    public function up()
    {
        $divisions = DB::table('divisions')->get();

        foreach ($divisions as $division) {
            $settings = $division->settings ? json_decode($division->settings, true) : [];

            $settings['max_platoon_leader_rank'] = 4;

            DB::table('divisions')
                ->where('id', $division->id)
                ->update(['settings' => json_encode($settings)]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * This method reverts the change by removing the
     * settings->max_platoon_leader_rank key from all divisions.
     *
     * @return void
     */
    public function down()
    {
        $divisions = DB::table('divisions')->get();

        foreach ($divisions as $division) {
            $settings = $division->settings ? json_decode($division->settings, true) : [];

            if (array_key_exists('max_platoon_leader_rank', $settings)) {
                unset($settings['max_platoon_leader_rank']);
            }

            DB::table('divisions')
                ->where('id', $division->id)
                ->update(['settings' => json_encode($settings)]);
        }
    }
};
