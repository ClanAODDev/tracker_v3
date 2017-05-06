<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->mediumInteger('clan_id')->unsigned();
            $table->tinyInteger('rank_id')->default(1);
            $table->mediumInteger('platoon_id');
            $table->mediumInteger('squad_id');
            $table->tinyInteger('position_id')->default(1);
            $table->timestamp('join_date')->nullable();
            $table->timestamp('last_forum_login')->nullable();
            $table->timestamp('last_promoted')->nullable();
            $table->mediumInteger('recruiter_id');
            $table->softDeletes();
            $table->timestamps();
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
