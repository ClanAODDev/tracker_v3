<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
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
