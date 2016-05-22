<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePositionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('icon');
            $table->string('class');
            $table->tinyInteger('order');
            $table->timestamps();
        });

        DB::table('positions')->insert(
            [
                ['name' => 'Member', 'icon' => '', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Squad Leader', 'icon' => 'fa fa-shield', 'class' => 'text-info', 'order' => 0],
                ['name' => 'Platoon Leader', 'icon' => 'fa fa-circle', 'class' => 'text-warning', 'order' => 0],
                ['name' => 'General Sergeant', 'icon' => 'fa', 'class' => 'text-default', 'order' => 0],
                ['name' => 'Executive Officer', 'icon' => 'fa fa-dot-circle-o', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Commanding Officer', 'icon' => 'fa fa-circle', 'class' => 'text-danger', 'order' => 0],
                ['name' => 'Clan Admin', 'icon' => 'fa fa-square', 'class' => 'text-danger', 'order' => 0],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('positions');
    }
}
