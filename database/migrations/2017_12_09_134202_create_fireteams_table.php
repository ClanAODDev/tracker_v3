<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFireteamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fireteams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('owner_id')->unsigned();
            $table->integer('players_needed');
            $table->integer('owner_light');
            $table->boolean('confirmed')->default(false);
            $table->dateTime('starts_at');
            $table->enum('type', [
                    'nightfall',
                    'strikes',
                    'trials of the nine',
                    'raid',
                    'crucible',
                    'down for anything'
                ]);
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
        Schema::dropIfExists('fireteams');
    }
}
