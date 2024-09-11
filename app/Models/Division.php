<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\Position;
use App\Presenters\DivisionPresenter;
use App\Settings\DivisionSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Class Division.
 */
class Division extends Model
{
    use HasFactory;
    use Notifiable;
    use RecordsActivity;
    use SoftDeletes;

    public array $defaultSettings = [

        /**
         * Discord specific settings
         */
        'slack_alert_created_member' => false,
        'slack_alert_removed_member' => false,
        'slack_alert_updated_member' => false,
        'slack_alert_created_request' => false,
        'slack_alert_division_edited' => false,
        'slack_alert_member_denied' => false,
        'slack_alert_member_approved' => false,
        'slack_alert_member_transferred' => false,
        'officer_channel' => '',
        'slack_alert_pt_member_removed' => false,

        /**
         * Recruiting and basic settings
         */
        'use_welcome_thread' => false,
        'division_structure' => '',
        'welcome_area' => '',
        'welcome_pm' => '',
        'inactivity_days' => 30,
        'activity_threshold' => [
            ['days' => 30, 'class' => 'text-danger'], ['days' => 14, 'class' => 'text-warning'],
        ],
        'recruiting_threads' => [
            ['thread_name' => 'AOD Code of Conduct', 'thread_id' => 3327, 'comments' => ''],
            ['thread_name' => 'AOD Ranking Structure', 'thread_id' => 3326, 'comments' => ''],
        ], 'recruiting_tasks' => [
            ['task_description' => 'Adjust forum profile settings'],
            ['task_description' => 'Copy TS identity unique id to forum profile'],
            ['task_description' => 'Change name on Teamspeak (add AOD_ and rank)'],
            ['task_description' => 'Reminder that forum login name will change in 24/48 hours'],
            ['task_description' => 'Introduce new member to the other members of the division'],
        ], 'locality' => [
            ['old-string' => 'squad', 'new-string' => 'squad'], ['old-string' => 'platoon', 'new-string' => 'platoon'],
            ['old-string' => 'squad leader', 'new-string' => 'squad leader'],
            ['old-string' => 'platoon leader', 'new-string' => 'platoon leader'],
        ],
    ];

    protected static array $recordEvents = ['created', 'deleted'];

    /**
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'settings' => 'json',
        'shutdown_at' => 'datetime',
    ];

    /**
     * @var array
     */
    protected $hidden = ['structure'];

    protected $withCount = ['sergeants', 'members'];

    /**
     * @var array
     */
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function (self $division) {
            $division->settings = $division->defaultSettings;
            $division->slug = Str::slug($division->name);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setAbbreviationAttribute($value): string
    {
        return $this->attributes['abbreviation'] = strtolower($value);
    }

    public function mismatchedTSMembers(): HasMany
    {
        return $this->members()
            ->where('join_date', '<', Carbon::today()->subDays(5))
            ->where(function ($query) {
                $query->where('last_ts_activity', null)
                    ->orWhere('last_ts_activity', '0000-00-00 00:00:00');
            });
    }

    public function misconfiguredDiscordMembers(): HasMany
    {
        return $this->membersOfDiscordState([
            'never_connected',
            'never_configured',
            'disconnected',
        ]);
    }

    public function membersOfDiscordState(array $state)
    {
        $validStates = [
            'connected',
            'never_connected',
            'disconnected',
            'never_configured',
        ];

        $invalidStates = array_diff($state, $validStates);

        if (! empty($invalidStates)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid Discord state: %s',
                implode(', ', $invalidStates)
            ));
        }

        return $this->members()
            ->whereIn('last_voice_status', $state)
            ->with(['rank', 'platoon']);
    }

    /**
     * relationship - division has many members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function newMembersLast30(): HasMany
    {
        return $this->hasMany(Member::class)->where('join_date', '>', Carbon::now()->subDays(30));
    }

    public function newMembersLast60(): HasMany
    {
        return $this->hasMany(Member::class)->where('join_date', '>', Carbon::now()->subDays(60));
    }

    public function newMembersLast90(): HasMany
    {
        return $this->hasMany(Member::class)->where('join_date', '>', Carbon::now()->subDays(90));
    }

    public function notes(): HasManyThrough
    {
        return $this->hasManyThrough(Note::class, Member::class)->with('member', 'author');
    }

    public function present(): DivisionPresenter
    {
        return new DivisionPresenter($this);
    }

    public function census(): HasMany
    {
        return $this->hasMany(Census::class);
    }

    public function squads(): HasManyThrough
    {
        return $this->hasManyThrough(Squad::class, Platoon::class);
    }

    public function routeNotificationForBot()
    {
        return $this->settings()->get('officer_channel');
    }

    /**
     * Division has many platoons.
     *
     * @return HasMany
     */
    public function platoons()
    {
        return $this->hasMany(Platoon::class)->orderBy('order');
    }

    /**
     * Division has many activity entries.
     *
     * @return HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Enabled division scope.
     *
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereActive(true)->orderBy('name', 'ASC');
    }

    public function scopeWithoutFloaters($query)
    {
        return $query->where('slug', '!=', 'floater');
    }

    /**
     * @return mixed
     */
    public function scopeShuttingDown($query, bool $includeShutdown = false)
    {
        if (! $includeShutdown) {
            return $query->whereNull('shutdown_at');
        }

        return $query;
    }

    /**
     * @return mixed
     */
    public function sergeants()
    {
        return $this->members()->where('rank_id', '>', 8);
    }

    /**
     * Count of Sgts and SSGs.
     */
    public function sgtAndSsgt()
    {
        return $this->members()->whereIn('rank_id', [9, 10]);
    }

    /**
     * @return mixed
     */
    public function membersActiveSinceDaysAgo($days)
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        return $this->members()->where('last_activity', '>=', $date);
    }

    /**
     * @return $this
     */
    public function membersActiveOnTsSinceDaysAgo($days)
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        return $this->members()->where('last_ts_activity', '>=', $date);
    }

    /**
     * @return $this
     */
    public function membersActiveOnDiscordSinceDaysAgo($days)
    {
        $date = Carbon::now()->subDays($days)->format('Y-m-d');

        return $this->members()->where('last_voice_activity', '>=', $date);
    }

    /**
     * Includes general sgt (4) and admin (7).
     *
     * @return mixed
     */
    public function generalSergeants()
    {
        return $this->members()->whereIn('position', [
            Position::CLAN_ADMIN,
            Position::GENERAL_SERGEANT,
        ]);
    }

    /**
     * @return BelongsTo
     */
    public function handle()
    {
        return $this->belongsTo(Handle::class);
    }

    /**
     * Gets unassigned members of a division (no platoon assignment)
     * NOTE: Only members (position 1).
     */
    public function unassigned()
    {
        return $this->members()
            ->where('platoon_id', 0)
            ->whereIn('position', [Position::MEMBER])->orderBy(
                'rank_id',
                'asc'
            )->orderBy('name', 'asc');
    }

    /**
     * @return string
     */
    public function locality($string)
    {
        $locality = collect($this->settings()->locality);
        if (! $locality->count()) {
            Log::error("No locality defaults were found for division {$this->name}");

            return ucwords($string);
        }
        $results = $locality->first(function ($translation) use ($string) {
            if (\array_key_exists('old-string', $translation)) {
                return $translation['old-string'] === strtolower($string);
            }
        });
        if (! $results) {
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
        $this->fireCustomModelEvent('settingsRead', true);

        return new DivisionSettings($this->settings, $this);
    }

    /**
     * Gets CO and XOs of a division.
     *
     * @return mixed
     */
    public function leaders()
    {
        return $this->members()->orderBy('position', 'desc')
            ->whereIn('position', [
                Position::EXECUTIVE_OFFICER,
                Position::COMMANDING_OFFICER,
            ]);
    }

    /**
     * Gets part time members of a division.
     */
    public function partTimeMembers()
    {
        return $this->belongsToMany(
            Member::class,
            'division_parttimer'
        )->orderByDesc('rank_id')->orderBy('name')->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->active;
    }

    public function getLogoPath()
    {
        if ($this->logo) {
            return asset(Storage::url($this->logo));
        }

        return asset(config('app.logo'));
    }

    public function isShutdown()
    {
        return $this->shutdown_at;
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'division_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return HasMany
     */
    public function memberRequests()
    {
        return $this->hasMany(MemberRequest::class);
    }

    private static function setMissingSettings(self $division): void
    {
        if ($diff = array_diff(array_keys($division->defaultSettings), array_keys($division->settings))) {
            foreach ($diff as $key) {
                $division->settings()->set($key, $division->defaultSettings[$key]);
            }
        }
    }
}
