<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        DB::table('divisions')->get()->each(function ($row) {
            $settings = json_decode($row->settings, true);

            if (isset($settings['chat_alerts']['rank_changed'])) {
                $settings['chat_alerts']['member_promoted'] = $settings['chat_alerts']['rank_changed'];
                unset($settings['chat_alerts']['rank_changed']);

                DB::table('divisions')
                    ->where('id', $row->id)
                    ->update(['settings' => json_encode($settings)]);
            }
        });
    }

    public function down()
    {
        DB::table('divisions')->get()->each(function ($row) {
            $settings = json_decode($row->settings, true);

            if (isset($settings['chat_alerts']['member_promoted'])) {
                $settings['chat_alerts']['rank_changed'] = $settings['chat_alerts']['member_promoted'];
                unset($settings['chat_alerts']['member_promoted']);

                DB::table('divisions')
                    ->where('id', $row->id)
                    ->update(['settings' => json_encode($settings)]);
            }
        });
    }
};
