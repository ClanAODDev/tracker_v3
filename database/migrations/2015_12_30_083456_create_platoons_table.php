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
            $table->integer('order')->default(0);
            $table->string('name');
            $table->mediumInteger('division_id');
            $table->mediumInteger('leader_id');
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
