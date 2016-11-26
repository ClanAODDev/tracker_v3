<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarhammerWowTf2Divisions extends Migration
{
    public $defaultSettings = [
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

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('divisions')->insert([
            [
                'name' => 'Warhammer',
                'abbreviation' => 'wh',
                'description' => 'Some random description here',
                'handle_id' => 0,
                'settings' => json_encode($this->defaultSettings),
                'active' => true,
            ],

            // World of Warcraft
            [
                'name' => 'World of Warcraft',
                'abbreviation' => 'wow',
                'description' => 'Some random description here',
                'handle_id' => 0,
                'settings' => json_encode($this->defaultSettings),
                'active' => true,
            ],

            // Titanfall 2
            [
                'name' => 'Titanfall 2',
                'abbreviation' => 'tf2',
                'description' => 'Some random description here',
                'handle_id' => 0,
                'settings' => json_encode($this->defaultSettings),
                'active' => true,
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
