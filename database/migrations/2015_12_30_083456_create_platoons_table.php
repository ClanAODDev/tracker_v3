<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlatoonsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platoons', function(Blueprint $table)
        {
            $table->increments('id');
            $table->mediumInteger('order');
            $table->string('name');
            $table->mediumInteger('division_id');
            $table->mediumInteger('platoon_leader_id');
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
        Schema::drop('platoons');
    }

}
