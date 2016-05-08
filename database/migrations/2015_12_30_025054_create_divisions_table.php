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
            $table->boolean('enabled')->default(0);
            $table->string('division_structure');
            $table->string('welcome_forum');
            $table->mediumInteger('handle_id');
            $table->timestamps();
        });

        $this->populateDivisions();
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

    public function populateDivisions()
    {
        DB::table('divisions')->insert(
            [
                // AOD Racing
                [
                    'name' => 'AOD Racing',
                    'abbreviation' => 'aodr',
                    'division_structure' => 103832,
                    'welcome_forum' => 544,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // ARK
                [
                    'name' => 'ARK',
                    'abbreviation' => 'ark',
                    'division_structure' => 128577,
                    'welcome_forum' => 533,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // Battlefield
                [
                    'name' => 'Battlefield',
                    'abbreviation' => 'bf',
                    'division_structure' => 73448,
                    'welcome_forum' => 458,
                    'handle_id' => 2,
                    'enabled' => 1
                ],

                // Battlefront
                [
                    'name' => 'Battlefront',
                    'abbreviation' => 'swb',
                    'division_structure' => 115653,
                    'welcome_forum' => 574,
                    'handle_id' => 2,
                    'enabled' => 1
                ],

                // Jedi Knight
                [
                    'name' => 'Jedi Knight',
                    'abbreviation' => 'jk',
                    'division_structure' => 62557,
                    'welcome_forum' => 123,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // Planetside 2
                [
                    'name' => 'Jedi Knight',
                    'abbreviation' => 'jk',
                    'division_structure' => 65422,
                    'welcome_forum' => 393,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // Skyforge
                [
                    'name' => 'Skyforge',
                    'abbreviation' => 'sf',
                    'division_structure' => 119785,
                    'welcome_forum' => 566,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // Tom Clancy
                [
                    'name' => 'Tom Clancy',
                    'abbreviation' => 'tc',
                    'division_structure' => 121653,
                    'welcome_forum' => 495,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // Warframe
                [
                    'name' => 'Warframe',
                    'abbreviation' => 'wf',
                    'division_structure' => 104706,
                    'welcome_forum' => 514,
                    'handle_id' => 0,
                    'enabled' => 1
                ],

                // War Thunder
                [
                    'name' => 'Warthunder',
                    'abbreviation' => 'wt',
                    'division_structure' => 64966,
                    'welcome_forum' => 432,
                    'handle_id' => 0,
                    'enabled' => 1
                ],
            ]
        );
    }
}
