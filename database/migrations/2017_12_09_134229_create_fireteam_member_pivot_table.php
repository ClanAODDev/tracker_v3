<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFireteamMemberPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fireteam_member', function (Blueprint $table) {
            $table->integer('fireteam_id')->unsigned()->index();
            $table->foreign('fireteam_id')->references('id')->on('fireteams')->onDelete('cascade');
            $table->integer('member_id')->unsigned()->index();
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->integer('light');
            $table->primary(['fireteam_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fireteam_member');
    }
}
