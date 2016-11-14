<?php

use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultLocality = [
            'squad' => 'squad',
            'platoon' => 'platoon',
            'squad leader' => 'squad leader',
            'platoon leader' => 'platoon leader',
        ];

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
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // ARK
                [
                    'name' => 'ARK',
                    'abbreviation' => 'ark',
                    'description' => 'Some random description here',
                    'division_structure' => 128577,
                    'welcome_forum' => 533,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Armored Warfare
                [
                    'name' => 'Armored Warfare',
                    'abbreviation' => 'aw',
                    'description' => 'Some random description here',
                    'division_structure' => 131206,
                    'welcome_forum' => 609,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Battlefield
                [
                    'name' => 'Battlefield',
                    'abbreviation' => 'bf',
                    'description' => 'Some random description here',
                    'division_structure' => 73448,
                    'welcome_forum' => 458,
                    'handle_id' => 2,
                    'active' => 1,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Battlefront
                [
                    'name' => 'Battlefront',
                    'abbreviation' => 'swb',
                    'description' => 'Some random description here',
                    'division_structure' => 115653,
                    'welcome_forum' => 574,
                    'handle_id' => 2,
                    'active' => 1,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Black Desert
                [
                    'name' => 'Black Desert',
                    'abbreviation' => 'bdo',
                    'description' => 'Some random description here',
                    'division_structure' => 128672,
                    'welcome_forum' => 598,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Floaters (SGM)
                [
                    'name' => 'Floater',
                    'abbreviation' => 'floater',
                    'description' => 'Some random description here',
                    'division_structure' => 0,
                    'welcome_forum' => 0,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Jedi Knight
                [
                    'name' => 'Jedi Knight',
                    'abbreviation' => 'jk',
                    'description' => 'Some random description here',
                    'division_structure' => 62557,
                    'welcome_forum' => 123,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Overwatch
                [
                    'name' => 'Overwatch',
                    'abbreviation' => 'ow',
                    'description' => 'Some random description here',
                    'division_structure' => 132965,
                    'welcome_forum' => 617,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Planetside 2
                [
                    'name' => 'Planetside 2',
                    'abbreviation' => 'ps2',
                    'description' => 'Some random description here',
                    'division_structure' => 65422,
                    'welcome_forum' => 393,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Skyforge
                [
                    'name' => 'Skyforge',
                    'abbreviation' => 'sf',
                    'description' => 'Some random description here',
                    'division_structure' => 119785,
                    'welcome_forum' => 566,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Tom Clancy
                [
                    'name' => 'Tom Clancy',
                    'abbreviation' => 'tc',
                    'description' => 'Some random description here',
                    'division_structure' => 121653,
                    'welcome_forum' => 495,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // Warframe
                [
                    'name' => 'Warframe',
                    'abbreviation' => 'wf',
                    'description' => 'Some random description here',
                    'division_structure' => 104706,
                    'welcome_forum' => 514,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
                // War Thunder
                [
                    'name' => 'War Thunder',
                    'abbreviation' => 'wt',
                    'description' => 'Some random description here',
                    'division_structure' => 64966,
                    'welcome_forum' => 432,
                    'handle_id' => 0,
                    'active' => 0,
                    'settings' => json_encode([]),
                    'locality' => json_encode($defaultLocality),
                ],
            ]
        );
    }
}
