<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDivisionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('abbreviation')->unique();
            $table->text('description');
            $table->string('division_structure');
            $table->string('welcome_forum');
            $table->mediumInteger('handle_id');
            $table->timestamps();
        });

        DB::table('divisions')->insert(
            [
                ['name' => 'Battlefield', 'abbreviation' => 'bf', 'description' => 'Battlefield 4', 'division_structure' => 73448, 'welcome_forum' => 458, 'handle_id' => 2],
                ['name' => 'Wargaming', 'abbreviation' => 'wg', 'description' => 'World of Tanks, World of Warships', 'division_structure' => 94236, 'welcome_forum' => 54564, 'handle_id' => 2],
                ['name' => 'Battlefront', 'abbreviation' => 'swb', 'description' => 'Star Wars: Battlefront', 'division_structure' => 115653, 'welcome_forum' => 574, 'handle_id' => 2],
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
        Schema::drop('divisions');
    }
}
