<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\DiscordStatus;
use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Member\HasCustomAttributes;
use App\Presenters\MemberPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Member extends Model
{
    use HasCustomAttributes;
    use HasFactory;
    use Notifiable;
    use RecordsActivity;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (Member $member) {
            $member->transfers()->pending()->delete();
        });
    }

    public function routeNotificationForBot(): ?string
    {
        return $this->discord;
    }

    protected $casts = [
        'pending_member'            => 'boolean',
        'flagged_for_inactivity'    => 'boolean',
        'join_date'                 => 'datetime',
        'last_activity'             => 'datetime',
        'last_voice_activity'       => 'datetime',
        'last_promoted_at'          => 'datetime',
        'last_trained_at'           => 'datetime',
        'xo_at'                     => 'datetime',
        'co_at'                     => 'datetime',
        'last_activity_reminder_at' => 'datetime',

        'discord_id' => 'string',

        'position'          => Position::class,
        'rank'              => Rank::class,
        'last_voice_status' => DiscordStatus::class,

        'groups' => 'array',
    ];

    protected $guarded = [];

    public function awards(): HasMany
    {
        return $this->hasMany(MemberAward::class, 'member_id')
            ->with(['award' => fn ($q) => $q->withCount('recipients')])
            ->where('approved', true);
    }

    public function present(): MemberPresenter
    {
        return new MemberPresenter($this);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function activityRemindedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activity_reminded_by_id');
    }

    public function activityReminders(): HasMany
    {
        return $this->hasMany(ActivityReminder::class, 'member_id')->latest();
    }

    public function latestActivityReminder(): HasOne
    {
        return $this->hasOne(ActivityReminder::class, 'member_id')->latestOfMany();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'member_id')->latest();
    }

    public function rankActions(): HasMany
    {
        return $this->hasMany(RankAction::class, 'member_id')->latest();
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'member_id')->latest();
    }

    public function squadLeaderOf(): HasOne
    {
        return $this->hasOne(Squad::class, 'leader_id');
    }

    public function reset(): void
    {
        $this->update([
            'division_id'            => 0,
            'platoon_id'             => 0,
            'squad_id'               => 0,
            'position'               => Position::MEMBER,
            'flagged_for_inactivity' => false,
            'groups'                 => null,
        ]);

        $this->transfers()->pending()->delete();
        $this->leave()->delete();
        $this->partTimeDivisions()->detach();

        $divisionSpecificTagIds = $this->tags()->whereNotNull('division_tags.division_id')->pluck('division_tags.id');
        $this->tags()->detach($divisionSpecificTagIds);
    }

    public function scopeUnassignedSquadLeaders(Builder $query): void
    {
        $query
            ->where('position', Position::SQUAD_LEADER)
            ->whereNotIn('clan_id', function ($q) {
                $q->select('leader_id')->from('squads')->whereNotNull('leader_id');
            });
    }

    public function scopeUnassignedPlatoonLeaders(Builder $query): void
    {
        $query
            ->where('position', Position::PLATOON_LEADER)
            ->whereNotIn('clan_id', function ($q) {
                $q->select('leader_id')->from('platoons')->whereNotNull('leader_id');
            });
    }

    public function partTimeDivisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_parttimer')->withTimestamps();
    }

    public function platoon(): BelongsTo
    {
        return $this->belongsTo(Platoon::class);
    }

    public function squad(): BelongsTo
    {
        return $this->belongsTo(Squad::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recruiter_id', 'clan_id');
    }

    public function recruits(): HasMany
    {
        return $this->hasMany(self::class, 'recruiter_id', 'clan_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'last_trained_by', 'clan_id');
    }

    public function leave(): HasOne
    {
        return $this->hasOne(Leave::class, 'member_id');
    }

    public function expiredLeave(): HasOne
    {
        return $this->hasOne(Leave::class, 'member_id')->where('end_date', '<', today());
    }

    public function memberRequest(): HasOne
    {
        return $this->hasOne(MemberRequest::class, 'member_id');
    }

    public function activeLeave(): HasOne
    {
        return $this->hasOne(Leave::class, 'member_id')->where('end_date', '>', today());
    }

    public function memberHandles(): HasMany
    {
        return $this->hasMany(MemberHandle::class);
    }

    public function handles(): BelongsToMany
    {
        return $this->belongsToMany(Handle::class)->withPivot('value');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DivisionTag::class, 'member_tag')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function isSquadLeader(Squad $squad): bool
    {
        return $this->clan_id === $squad->leader_id;
    }

    public function isPlatoonLeader(Platoon $platoon): bool
    {
        return $this->clan_id === $platoon->leader_id;
    }

    public function isDivisionLeader(Division $division): bool
    {
        return $this->division->id === $division->id && in_array($this->position, [
            Position::EXECUTIVE_OFFICER,
            Position::COMMANDING_OFFICER,
        ], true);
    }

    public function scopeMisconfiguredDiscord(Builder $query): void
    {
        $query->whereIn('last_voice_status', [
            DiscordStatus::NEVER_CONNECTED,
            DiscordStatus::NEVER_CONFIGURED,
            DiscordStatus::DISCONNECTED,
        ]);
    }

    public function getDiscordUrl(): ?string
    {
        if (! $this->discord_id) {
            return null;
        }

        return sprintf('https://discordapp.com/users/%s', $this->discord_id);
    }

    public function getDiscordAvatarUrl(): ?string
    {
        if (! $this->discord_id) {
            return null;
        }

        if ($this->discord_avatar) {
            return sprintf('https://cdn.discordapp.com/avatars/%s/%s.png?size=64', $this->discord_id, $this->discord_avatar);
        }

        return sprintf('https://cdn.discordapp.com/embed/avatars/%d.png', abs((int) $this->discord_id >> 22) % 6);
    }

    public function isRank(array|Rank $rank): bool
    {
        if (! $this->rank instanceof Rank) {
            return false;
        }

        if (is_array($rank)) {
            return in_array($this->rank, $rank, true);
        }

        return $this->rank === $rank;
    }

    public function getUrlParams(): array
    {
        return [$this->clan_id, $this->rank->getAbbreviation() . '-' . $this->name];
    }

    public function memberRequests(): HasMany
    {
        return $this->hasMany(MemberRequest::class, 'requester_id');
    }

    public static function isValidForumName(string $name): bool
    {
        return ! preg_match('/[<>&"\']/u', $name);
    }

    public function botResponse(): array
    {
        $division = ($this->division)
            ? "{$this->division->name} Division"
            : 'Ex-AOD';

        $memberLink = route('member', $this->getUrlParams());

        $links = [
            "[Forum]({$this->AODProfileLink})",
            "[Tracker]({$memberLink})",
        ];

        $properties = [
            "Discord activity: {$this->present()->lastActive('last_voice_activity', ['weeks', 'months'])}",
            'Discord Username: ' . ($this->discord ?? 'Not set'),
        ];

        return [
            'name'  => "{$this->present()->rankName} ({$this->clan_id}) - {$division}",
            'value' => 'Profiles: '
                . implode(', ', $links)
                . PHP_EOL
                . implode(PHP_EOL, $properties),
        ];
    }

    public function scopeEligibleForRankAction(Builder $query, $user, ?string $search = null): Builder
    {
        $currentMember = $user->member;
        $roleLimits    = [
            'squadLeader'    => config('aod.rank.max_squad_leader'),
            'platoonLeader'  => config('aod.rank.max_platoon_leader'),
            'divisionLeader' => config('aod.rank.max_division_leader'),
        ];

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query
            ->where('id', '<>', $currentMember->id)
            ->when($user->isMember() || $user->isSquadLeader(), fn (Builder $query) => $query
                ->where('squad_id', $currentMember->squad_id)
                ->where('rank', '<', $roleLimits['squadLeader'])
            )
            ->when($user->isPlatoonLeader(), fn (Builder $query) => $query
                ->where('platoon_id', $currentMember->platoon_id)
                ->where('rank', '<', $roleLimits['platoonLeader'])
            )
            ->when($user->isDivisionLeader() && ! $user->isRole('admin'), fn (Builder $query) => $query
                ->where('division_id', $currentMember->division_id)
                ->where('rank', '<', $roleLimits['divisionLeader'])
            )
            ->when($user->isRole('admin'), fn (Builder $query) => $query
                ->where('division_id', '!=', 0)
            )
            ->where('rank', '<=', $currentMember->rank->value);
    }
}
