<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Presenters\DivisionPresenter;
use App\Settings\DivisionSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * Settings to expose to Division API
     */
    public array $exposedSettings = [
        'always_visible_in_discord',
        'meta_description',
    ];

    /**
     * Initial division settings to define on creation
     */
    public array $defaultSettings = [

        'officer_channel' => '',
        'member_channel' => '',

        /**
         * Recruiting and basic settings
         */
        'use_welcome_thread' => false,
        'division_structure' => '',
        'welcome_area' => '',
        'welcome_pm' => '',
        'inactivity_days' => 30,
        'recruitment_rss_feed' => '',

        'activity_threshold' => [
            ['days' => 30, 'class' => 'text-danger'], ['days' => 14, 'class' => 'text-warning'],
        ],

        'max_platoon_leader_rank' => Rank::PRIVATE_FIRST_CLASS,

        'chat_alerts' => [
            'division_edited' => false,
            'member_approved' => false,
            'member_awarded' => false,
            'member_created' => false,
            'member_denied' => false,
            'member_removed' => false,
            'member_transferred' => false,
            'pt_member_removed' => false,
            'rank_changed' => false,
            'request_created' => false,
        ],

        'recruiting_threads' => [
            ['thread_name' => 'AOD Code of Conduct', 'thread_url' => 'https://www.clanaod.net/forums/showthread.php?t=3327', 'comments' => ''],
            ['thread_name' => 'AOD Ranking Structure', 'thread_url' => 'https://www.clanaod.net/forums/showthread.php?t=3326', 'comments' => ''],
        ],

        'recruiting_tasks' => [
            ['task_description' => 'Adjust forum profile settings'],
            ['task_description' => 'Copy TS identity unique id to forum profile'],
            ['task_description' => 'Change name on Teamspeak (add AOD_ and rank)'],
            ['task_description' => 'Reminder that forum login name will change in 24/48 hours'],
            ['task_description' => 'Introduce new member to the other members of the division'],
        ],

        'locality' => [
            ['old-string' => 'squad', 'new-string' => 'squad'],
            ['old-string' => 'platoon', 'new-string' => 'platoon'],
            ['old-string' => 'squad leader', 'new-string' => 'squad leader'],
            ['old-string' => 'platoon leader', 'new-string' => 'platoon leader'],
        ],
    ];

    protected $casts = [
        'active' => 'boolean',
        'show_on_site' => 'boolean',
        'settings' => 'json',
        'screenshots' => 'array',
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

    protected static function booted(): void
    {
        static::creating(function (self $division) {
            $division->settings = $division->defaultSettings;
            $division->slug = Str::slug($division->name);
        });

        static::created(fn (Division $division) => $division->recordActivity(ActivityType::CREATED_DIVISION));
        static::deleted(fn (Division $division) => $division->recordActivity(ActivityType::DELETED_DIVISION));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setAbbreviationAttribute($value): string
    {
        return $this->attributes['abbreviation'] = strtolower($value);
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

    public function latestCensus(): HasOne
    {
        return $this->hasOne(Census::class)->latestOfMany();
    }

    public function squads(): HasManyThrough
    {
        return $this->hasManyThrough(Squad::class, Platoon::class);
    }

    public function routeNotificationForMembers(): ?string
    {
        return $this->settings()->get('member_channel', '')
            ?: ($this->abbreviation ? $this->abbreviation . '-members' : null);
    }

    public function routeNotificationForOfficers(): ?string
    {
        return $this->settings()->get('officer_channel', '')
            ?: ($this->abbreviation ? $this->abbreviation . '-officers' : null);
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
        $query->whereActive(true)->orderBy('name', 'ASC');
    }

    public function scopeWithoutFloaters($query)
    {
        $query->whereNotIn('slug', ['floater', 'bluntz-reserves']);
    }

    /**
     * @return mixed
     */
    public function scopeShuttingDown($query, bool $includeShutdown = false)
    {
        if (! $includeShutdown) {
            $query->whereNull('shutdown_at');
        }
    }

    /**
     * @return mixed
     */
    public function sergeants()
    {
        return $this->members()->where('rank', '>=', Rank::SERGEANT);
    }

    /**
     * Count of Sgts and SSGs.
     */
    public function sgtAndSsgt()
    {
        return $this->members()->whereIn('rank', [
            Rank::SERGEANT,
            Rank::STAFF_SERGEANT,
        ]);
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
                'rank',
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

        $mergedSettings = array_merge($this->defaultSettings, $this->settings ?? []);

        return new DivisionSettings($mergedSettings, $this);
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
        )->orderByDesc('rank')->orderBy('name')->withTimestamps();
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
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset(Storage::url($this->logo));
        }

        return getThemedLogoPath();
    }

    public function isShutdown()
    {
        return $this->shutdown_at;
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'division_id')
            ->orderBy('created_at', 'desc');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(DivisionTag::class)->orderBy('name');
    }

    public function memberAwards(): HasManyThrough
    {
        return $this->hasManyThrough(MemberAward::class, Award::class, 'division_id', 'award_id');
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
