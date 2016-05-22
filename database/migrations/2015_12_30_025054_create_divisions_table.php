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
            $table->string('description');
            $table->boolean('enabled')->default(0);
            $table->string('division_structure');
            $table->string('welcome_forum');
            $table->mediumInteger('handle_id');
            $table->json('settings');
            $table->json('locality');
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
                    'name' => 'Project Cars',
                    'abbreviation' => 'pc',
                    'description' => 'Some random description here',
                    'division_structure' => 103832,
                    'welcome_forum' => 544,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // ARK
                [
                    'name' => 'ARK',
                    'abbreviation' => 'ark',
                    'description' => 'Some random description here',
                    'division_structure' => 128577,
                    'welcome_forum' => 533,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Battlefield
                [
                    'name' => 'Battlefield',
                    'abbreviation' => 'bf',
                    'description' => 'Some random description here',
                    'division_structure' => 73448,
                    'welcome_forum' => 458,
                    'handle_id' => 2,
                    'enabled' => 1,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Battlefront
                [
                    'name' => 'Battlefront',
                    'abbreviation' => 'swb',
                    'description' => 'Some random description here',
                    'division_structure' => 115653,
                    'welcome_forum' => 574,
                    'handle_id' => 2,
                    'enabled' => 1,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Jedi Knight
                [
                    'name' => 'Jedi Knight',
                    'abbreviation' => 'jk',
                    'description' => 'Some random description here',
                    'division_structure' => 62557,
                    'welcome_forum' => 123,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Overwatch
                [
                    'name' => 'Overwatch',
                    'abbreviation' => 'ow',
                    'description' => 'Some random description here',
                    'division_structure' => 132965,
                    'welcome_forum' => 617,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Planetside 2
                [
                    'name' => 'Planetside 2',
                    'abbreviation' => 'ps2',
                    'description' => 'Some random description here',
                    'division_structure' => 65422,
                    'welcome_forum' => 393,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Skyforge
                [
                    'name' => 'Skyforge',
                    'abbreviation' => 'sf',
                    'description' => 'Some random description here',
                    'division_structure' => 119785,
                    'welcome_forum' => 566,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Tom Clancy
                [
                    'name' => 'Tom Clancy',
                    'abbreviation' => 'tc',
                    'description' => 'Some random description here',
                    'division_structure' => 121653,
                    'welcome_forum' => 495,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // Warframe
                [
                    'name' => 'Warframe',
                    'abbreviation' => 'wf',
                    'description' => 'Some random description here',
                    'division_structure' => 104706,
                    'welcome_forum' => 514,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],

                // War Thunder
                [
                    'name' => 'War Thunder',
                    'abbreviation' => 'wt',
                    'description' => 'Some random description here',
                    'division_structure' => 64966,
                    'welcome_forum' => 432,
                    'handle_id' => 0,
                    'enabled' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode([
                        'squad' => 'squad',
                        'platoon' => 'platoon',
                        'squad leader' => 'squad leader',
                        'platoon leader' => 'platoon leader',
                    ]),
                ],
            ]
        );
    }
}
