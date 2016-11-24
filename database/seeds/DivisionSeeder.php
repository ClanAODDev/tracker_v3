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
        $defaultSettings = [
            'use_welcome_thread' => false,
            'division_structure' => '',
            'welcome_area' => '',
        ];

        DB::table('divisions')->insert(
            [
                // AOD Racing
                [
                    'name' => 'AOD Racing',
                    'abbreviation' => 'pc',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // ARK
                [
                    'name' => 'ARK',
                    'abbreviation' => 'ark',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Armored Warfare
                [
                    'name' => 'Armored Warfare',
                    'abbreviation' => 'aw',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Battlefield
                [
                    'name' => 'Battlefield',
                    'abbreviation' => 'bf',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Battlefront
                [
                    'name' => 'Battlefront',
                    'abbreviation' => 'swb',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Black Desert
                [
                    'name' => 'Black Desert',
                    'abbreviation' => 'bdo',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Floaters (SGM)
                [
                    'name' => 'Floater',
                    'abbreviation' => 'floater',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Jedi Knight
                [
                    'name' => 'Jedi Knight',
                    'abbreviation' => 'jk',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Overwatch
                [
                    'name' => 'Overwatch',
                    'abbreviation' => 'ow',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Planetside 2
                [
                    'name' => 'Planetside 2',
                    'abbreviation' => 'ps2',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Skyforge
                [
                    'name' => 'Skyforge',
                    'abbreviation' => 'sf',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Tom Clancy
                [
                    'name' => 'Tom Clancy',
                    'abbreviation' => 'tc',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // Warframe
                [
                    'name' => 'Warframe',
                    'abbreviation' => 'wf',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
                // War Thunder
                [
                    'name' => 'War Thunder',
                    'abbreviation' => 'wt',
                    'description' => 'Some random description here',
                    'handle_id' => 0,
                    'settings' => json_encode($defaultSettings),
                    'active' => true,
                ],
            ]
        );
    }
}
