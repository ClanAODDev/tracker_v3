<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\ActivityType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Presenters\DivisionPresenter;
use App\Settings\DivisionSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Division extends Model
{
    use HasFactory;
    use Notifiable;
    use RecordsActivity;
    use SoftDeletes;

    public array $exposedSettings = [
        'always_visible_in_discord',
        'meta_description',
    ];

    public array $defaultSettings = [
        'officer_channel' => '',
        'member_channel'  => '',

        'use_welcome_thread'   => false,
        'division_structure'   => '',
        'welcome_area'         => '',
        'welcome_pm'           => '',
        'inactivity_days'      => 30,
        'recruitment_rss_feed' => '',

        'activity_threshold' => [
            ['days' => 30, 'class' => 'text-danger'], ['days' => 14, 'class' => 'text-warning'],
        ],

        'max_platoon_leader_rank' => Rank::PRIVATE_FIRST_CLASS,

        'chat_alerts' => [
            'division_edited'    => false,
            'member_applied'     => false,
            'member_approved'    => false,
            'member_awarded'     => false,
            'member_created'     => false,
            'member_denied'      => false,
            'member_promoted'    => false,
            'member_removed'     => false,
            'member_transferred' => false,
            'pt_member_removed'  => false,
            'rank_changed'       => false,
            'request_created'    => false,
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

        'always_visible_in_discord' => false,
        'meta_description'          => '',
    ];

    protected $casts = [
        'active'       => 'boolean',
        'show_on_site' => 'boolean',
        'settings'     => 'json',
        'screenshots'  => 'array',
        'shutdown_at'  => 'datetime',
    ];

    protected $hidden = ['structure'];

    protected $withCount = ['sergeants', 'members'];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $division) {
            $division->settings = $division->defaultSettings;
            $division->slug     = Str::slug($division->name);
        });

        static::created(function (Division $division) {
            $division->recordActivity(ActivityType::CREATED_DIVISION);
            $division->applicationFields()->createMany(DivisionApplicationField::DEFAULTS);
        });
        static::deleted(fn (Division $division) => $division->recordActivity(ActivityType::DELETED_DIVISION));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setAbbreviationAttribute($value): void
    {
        $this->attributes['abbreviation'] = strtolower($value);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
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

    public function leaderboardSnapshots(): HasMany
    {
        return $this->hasMany(LeaderboardSnapshot::class);
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

    public function platoons(): HasMany
    {
        return $this->hasMany(Platoon::class)->orderBy('order');
    }

    public function activity(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function scopeActive($query): void
    {
        $query->whereActive(true)->orderBy('name', 'ASC');
    }

    public function scopeWithoutFloaters($query): void
    {
        $query->whereNotIn('slug', ['floater']);
    }

    public function scopeWithoutBR($query): void
    {
        $query->whereNotIn('slug', ['bluntz-reserves']);
    }

    public function scopeShuttingDown($query, bool $includeShutdown = false): void
    {
        if (! $includeShutdown) {
            $query->whereNull('shutdown_at');
        }
    }

    public function sergeants(): HasMany
    {
        return $this->members()->where('rank', '>=', Rank::SERGEANT);
    }

    public function sgtAndSsgt(): HasMany
    {
        return $this->members()->whereIn('rank', [
            Rank::SERGEANT,
            Rank::STAFF_SERGEANT,
        ]);
    }

    public function membersActiveSinceDaysAgo(int $days): HasMany
    {
        return $this->members()->where('last_activity', '>=', now()->subDays($days)->toDateString());
    }

    public function membersActiveOnDiscordSinceDaysAgo(int $days): HasMany
    {
        return $this->members()->where('last_voice_activity', '>=', now()->subDays($days)->toDateString());
    }

    public function handle(): BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }

    public function unassigned(): HasMany
    {
        return $this->members()
            ->where('platoon_id', 0)
            ->whereIn('position', [Position::MEMBER])
            ->orderBy('rank', 'asc')
            ->orderBy('name', 'asc');
    }

    public function locality(string $string): string
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

    public function settings(): DivisionSettings
    {
        $this->fireCustomModelEvent('settingsRead', true);

        $mergedSettings = array_merge($this->defaultSettings, $this->settings ?? []);

        return new DivisionSettings($mergedSettings, $this);
    }

    public function leaders(): HasMany
    {
        return $this->members()->orderBy('position', 'desc')
            ->whereIn('position', [
                Position::EXECUTIVE_OFFICER,
                Position::COMMANDING_OFFICER,
            ]);
    }

    public function partTimeMembers(): BelongsToMany
    {
        return $this->belongsToMany(
            Member::class,
            'division_parttimer'
        )->orderByDesc('rank')->orderBy('name')->withTimestamps();
    }

    public function isShutdown(): bool
    {
        return (bool) $this->shutdown_at;
    }

    public function getLogoPath(): string
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset(Storage::url($this->logo));
        }

        return getThemedLogoPath();
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

    public function applicationFields(): HasMany
    {
        return $this->hasMany(DivisionApplicationField::class)->orderBy('display_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(DivisionApplication::class);
    }

    public function memberAwards(): HasManyThrough
    {
        return $this->hasManyThrough(MemberAward::class, Award::class, 'division_id', 'award_id');
    }

    public function memberRequests(): HasMany
    {
        return $this->hasMany(MemberRequest::class);
    }
}
