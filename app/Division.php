<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Presenters\DivisionPresenter;
use App\Settings\DivisionSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

/**
 * Class Division
 *
 * @package App
 */
class Division extends Model
{
    use RecordsActivity;

    /**
     * @var array
     */
    protected static $recordEvents = ['created', 'deleted'];

    /**
     * @var array
     */
    public $defaultSettings = [
        'slack_alert_created_member' => false,
        'slack_alert_removed_member' => false,
        'slack_alert_updated_member' => false,
        'slack_alert_created_request' => false,
        'slack_alert_division_edited' => false,
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
                'thread_id' => 3327,
                'comments' => ''
            ],
            [
                'thread_name' => 'AOD Ranking Structure',
                'thread_id' => 3326,
                'comments' => ''
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
                'task_description' => 'Change name on Teamspeak (add AOD_ and rank)',
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

    use Division\HasCustomAttributes;
    use RecordsActivity;
    use SoftDeletes;
    use Notifiable;

    /**
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'settings' => 'json',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'structure'
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'settings',
        'name',
        'handle_id',
        'description',
        'active',
        'abbreviation'
    ];

    /**
     * @param $string
     * @param $thread
     * @return bool
     */
    public static function threadCheck($string, $thread)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $thread . "&goto=newpost");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $getPosts = curl_exec($ch);
        $countPosts = stripos($getPosts, $string);
        if ( ! $countPosts) {
            $url = parse_url(curl_last_url($ch));
            $query = $url['query'];
            parse_str($query, $url_array);
            $page = @$url_array['page'] - 1;
            curl_setopt($ch, CURLOPT_URL, $thread . "&page={$page}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $getPosts = curl_exec($ch);
            $countPosts = stripos($getPosts, $string);
        }

        return ($countPosts) ? true : false;
    }

    /**
     * @return DivisionPresenter
     */
    public function present()
    {
        return new DivisionPresenter($this);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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
     * @return mixed
     */
    public function routeNotificationForSlack()
    {
        return config('app.aod.slack_webhook');
    }

    /**
     * Division has many platoons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function platoons()
    {
        return $this->hasMany(Platoon::class)->orderBy('order');
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
        return $this->belongsToMany(Member::class, 'division_parttimer')
            ->orderByDesc('rank_id')
            ->orderBy('name')
            ->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function sergeants()
    {
        return $this->members()->where('rank_id', '>', 8);
    }

    /**
     * relationship - division has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * @param $days
     * @return mixed
     */
    public function membersActiveSinceDaysAgo($days)
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        return $this->members()
            ->where('last_activity', '>=', $date);
    }

    /**
     * @return mixed
     */
    public function generalSergeants()
    {
        return $this->members()->wherePositionId(4);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function staffSergeants()
    {
        return $this->belongsToMany(Member::class, 'division_staffSergeants')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function handle()
    {
        return $this->belongsTo(Handle::class);
    }

    /**
     * Get default tags as well as division tags
     */
    public function availableTags()
    {
        return $this->tags()
            ->whereDivisionId($this->id)
            ->orWhere('default', true);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * Gets unassigned members of a division (no platoon assignment)
     * NOTE: Only members (position 1)
     */
    public function unassigned()
    {
        return $this->members()
            ->where('platoon_id', 0)
            ->whereIn('position_id', [1])
            ->orderBy('rank_id', 'asc')
            ->orderBy('name', 'asc');
    }

    /**
     * @param $string
     * @return string
     */
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
     * @return DivisionSettings
     */
    public function settings()
    {
        return new DivisionSettings($this->settings, $this);
    }

    /**
     * Gets CO and XOs of a division
     *
     * @return mixed
     */
    public function leaders()
    {
        return $this->members()
            ->orderBy('position_id', 'desc')
            ->whereIn('position_id', [5, 6]);
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }
}
