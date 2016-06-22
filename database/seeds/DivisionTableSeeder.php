<?php

use Illuminate\Database\Seeder;

class DivisionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('divisions')->insert(
            [
                // AOD Racing
                [
                    'name' => 'AOD Racing',
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
