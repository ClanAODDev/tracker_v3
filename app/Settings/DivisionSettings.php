<?php

namespace App\Settings;

use App\Division;

class DivisionSettings
{

    use Settable;

    protected $division;

    protected $defaultSettings = [
        'slack_alert_created_member' => false,
        'slack_alert_removed_member' => false,
        'slack_alert_updated_member' => false,
        'slack_alert_created_request' => false,
        'slack_channel' => '',
        'use_welcome_thread' => false,
        'division_structure' => '',
        'welcome_area' => '',
        'welcome_pm' => '',
        'activity_threshold' => [
            [
                'days' => 30,
                'class' => 'text-danger'
            ],
            [
                'days' => 14,
                'class' => 'text-warning'
            ],
        ],
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
        ]
    ];

    /**
     * Settings constructor.
     * @param array $settings
     * @param DivisionSettings|Division $division
     */
    public function __construct(array $settings, Division $division)
    {
        $this->settings = $settings;
        $this->division = $division;
    }

    protected function persist()
    {
        return $this->division->update(['settings' => $this->settings]);
    }

    public function resetToDefault()
    {
        return $this->division->update(['settings' => $this->defaultSettings]);
    }
}
