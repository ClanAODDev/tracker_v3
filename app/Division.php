<?php

namespace App;

use Carbon;
use Exception;
use App\Settings\DivisionSettings;
use App\Activities\RecordsActivity;
use App\Presenters\DivisionPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Division extends Model
{

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'json',
    ];

    use Division\HasCustomAttributes;
    use RecordsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];

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
     * @return DivisionPresenter
     */
    public function present()
    {
        return new DivisionPresenter($this);
    }

    public function census()
    {
        return $this->hasMany(Census::class);
    }

    /**
     * Get division's squads
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function squads()
    {
        return $this->hasManyThrough(Squad::class, Platoon::class);
    }


    /**
     * Division has many platoons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function platoons()
    {
        return $this->hasMany(Platoon::class);
    }


    /**
     * Division has many activity entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Enabled division scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    /**
     * Gets part time members of a division
     */
    public function partTimeMembers()
    {
        return $this->members()->wherePivot('primary', false);
    }

    /**
     * relationship - division has many members
     */
    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('primary')->withTimestamps();
    }

    /**
     * Gets active members of a division
     */
    public function activeMembers()
    {
        return $this->members()->wherePivot('primary', true);
    }

    public function membersActiveSinceDaysAgo($days)
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        return $this->activeMembers()
            ->where('last_activity', '>=', $date);
    }

    public function generalSergeants()
    {
        return $this->activeMembers()
            ->wherePositionId(4);
    }

    public function staffSergeants()
    {
        return $this->belongsToMany(Member::class, 'division_staffSergeants');
    }

    public function handles()
    {
        return $this->belongsToMany(Handle::class);
    }

    /**
     * Gets unassigned members of a division (no platoon assignment)
     * NOTE: Only members (position 1)
     */
    public function unassigned()
    {
        return $this->members()
            ->where('platoon_id', 0)
            ->whereIn('position_id', [1]);
    }

    /**
     * @return DivisionSettings
     */
    public function settings()
    {
        return new DivisionSettings($this->settings, $this);
    }

    public function locality($string)
    {

        $locality = collect($this->settings()->locality);

        if ( ! $locality->count()) {
            Log::error("No locality defaults were found for division {$this->name}");

            return ucwords($string);
        }

        $results = $locality->first(function ($translation) use ($string) {
            if (array_key_exists('old-string', $translation)) {
                return $translation['old-string'] == strtolower($string);
            }
        });

        if ( ! $results) {
            Log::error("The {$string} locality does not exist");

            return ucwords($string);
        }

        return ucwords($results['new-string']);
    }

    /**
     * Gets CO and XOs of a division
     *
     * @return mixed
     */
    public function leaders()
    {
        return $this->activeMembers()
            ->whereIn('position_id', [5, 6]);
    }

    public function isActive()
    {
        return $this->active;
    }
}
