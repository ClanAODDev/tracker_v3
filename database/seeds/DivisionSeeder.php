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
            'slack_alert_created_member' => false,
            'slack_alert_removed_member' => false,
            'slack_alert_updated_member' => false,
            'slack_alert_created_request' => false,
            'slack_channel' => '',
            'use_welcome_thread' => false,
            'division_structure' => '',
            'welcome_area' => '',
            'welcome_pm' => '',
            'recruiting_threads' => [
                [
                    'thread_name' => 'AOD Code of Conduct',
                    'thread_id' => 3327
                ],
                [
                    'thread_name' => 'AOD Ranking Structure',
                    'thread_id' => 3326
                ],
            ],
            'recruiting_tasks' => [
                [
                    'task_description' => 'Adjust forum profile settings'
                ],
                [
                    'task_description' => 'Copy TS identity unique id to forum profile',
                ],
                [
                    'task_description' => 'Change name on Teamspeak: %%member_name%%',
                ],
                [
                    'task_description' => 'Reminder that forum login name will change in 24/48 hours',
                ],
                [
                    'task_description' => 'Introduce new member to the other members of the division',
                ],
            ],

            'locality' => [
                [
                    'old-string' => 'squad',
                    'new-string' => 'squad'
                ],
                [
                    'old-string' => 'platoon',
                    'new-string' => 'platoon'
                ],
                [
                    'old-string' => 'squad leader',
                    'new-string' => 'squad leader'
                ],
                [
                    'old-string' => 'platoon leader',
                    'new-string' => 'platoon leader'
                ],
                [
                    'old-string' => 'member',
                    'new-string' => 'member'
                ],
                [
                    'old-string' => 'general sergeant',
                    'new-string' => 'general sergeant'
                ],
                [
                    'old-string' => 'executive officer',
                    'new-string' => 'executive officer'
                ],
                [
                    'old-string' => 'commanding officer',
                    'new-string' => 'commanding officer'
                ],
                [
                    'old-string' => 'clan admin',
                    'new-string' => 'clan admin'
                ],
            ]
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


