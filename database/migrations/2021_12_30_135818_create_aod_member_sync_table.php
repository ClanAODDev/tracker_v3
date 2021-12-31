<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAodMemberSyncTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection('sqlite')->dropIfExists('aod_member_sync');
        Schema::connection('sqlite')->create('aod_member_sync', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userid');
            $table->string('username');
            $table->date('joindate');
            $table->string('lastvisit');
            $table->string('lastvisit_time');
            $table->string('lastactivity');
            $table->string('lastactivity_time');
            $table->string('lastpost');
            $table->string('lastpost_time');
            $table->integer('postcount');
            $table->string('tsid');
            $table->string('lastts_connect');
            $table->string('lastts_connect_time');
            $table->string('aodrank');
            $table->integer('aodrankval');
            $table->string('aoddivision');
            $table->string('aodstatus');
            $table->string('discordtag');
            $table->boolean('allow_export');
            $table->boolean('allow_pm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::connection('sqlite')->dropIfExists('aod_member_sync');
    }
}
