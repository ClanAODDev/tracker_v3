<?php

namespace App\Models;

use App\Activities\RecordsActivity;
use App\Enums\Position;
use App\Models\Member\HasCustomAttributes;
use App\Presenters\MemberPresenter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Member.
 *
 * @method static whereName($name)
 */
class Member extends Model
{
    use HasCustomAttributes;
    use HasFactory;
    use RecordsActivity;
    use SoftDeletes;

    public const UNVERIFIED_EMAIL_GROUP_ID = 3;

    protected static array $recordEvents = [];

    protected $casts = [
        'pending_member' => 'boolean',
        'flagged_for_inactivity' => 'boolean',
        'join_date' => 'datetime',
        'last_activity' => 'datetime',
        'last_ts_activity' => 'datetime',
        'last_voice_activity' => 'datetime',
        'last_promoted_at' => 'datetime',
        'last_trained_at' => 'datetime',
        'xo_at' => 'datetime',
        'co_at' => 'datetime',

        'position' => \App\Enums\Position::class,
        'rank' => \App\Enums\Rank::class,
    ];

    protected $guarded = [];

    public function present(): MemberPresenter
    {
        return new MemberPresenter($this);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'member_id')
            ->orderBy('created_at', 'desc');
    }

    public function rankActions()
    {
        return $this->hasMany(RankAction::class, 'member_id')
            ->orderBy('created_at', 'desc');
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'member_id')->orderBy('created_at', 'desc');
    }

    public function assignPosition($position)
    {
        // reset assignments for specific positions
        if (\in_array(
            $position,
            [Position::COMMANDING_OFFICER, Position::EXECUTIVE_OFFICER, Position::GENERAL_SERGEANT, Position::CLAN_ADMIN],
            true
        )) {
            $this->platoon_id = 0;
            $this->squad_id = 0;
        }

        if ($position === Position::EXECUTIVE_OFFICER) {
            $this->xo_at = now();
            $this->co_at = null;
        }
        if ($position === Position::COMMANDING_OFFICER) {
            $this->co_at = now();
            $this->xo_at = null;
        }

        $this->position = $position;

        $this->save();
    }

    /**
     * @return Model
     */
    public function assignRank($rank)
    {
        return $this->rank()->associate(Rank::whereName(strtolower($rank))->firstOrFail());
    }

    /**
     * Enforce a singleton relationship for squad leaders.
     *
     * Prevents members from being a squad leader of more than one squad
     *
     * @return HasOne
     */
    public function squadLeaderOf()
    {
        return $this->hasOne(Squad::class, 'leader_id');
    }

    /**
     * Resets member's positions and division assignments
     * including part-time divisions.
     */
    public function resetPositionAndAssignments()
    {
        $this->update([
            'division_id' => 0, 'platoon_id' => 0, 'squad_id' => 0, 'position' => Position::MEMBER,
            'flagged_for_inactivity' => false,
            'groups' => null,
        ]);
    }

    /**
     * Handle Staff Sergeant assignments
     * division/.
     *
     * @return BelongsToMany
     */
    public function partTimeDivisions()
    {
        return $this->belongsToMany(Division::class, 'division_parttimer')->withTimestamps();
    }

    /**
     * relationship - member belongs to a platoon.
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    /**
     * relationship - member belongs to a squad.
     */
    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * relationship - member has many divisions.
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * @return BelongsTo
     */
    public function recruiter()
    {
        return $this->belongsTo(self::class, 'recruiter_id', 'clan_id');
    }

    public function recruits()
    {
        return $this->hasMany(self::class, 'recruiter_id', 'clan_id');
    }

    public function trainer()
    {
        return $this->belongsTo(self::class, 'last_trained_by', 'clan_id');
    }

    /**
     * @return HasOne
     */
    public function leave()
    {
        return $this->hasOne(Leave::class, 'member_id', 'clan_id');
    }

    /**
     * @return mixed
     */
    public function expiredLeave()
    {
        return $this->hasOne(Leave::class)->where('end_date', '<', \Carbon\Carbon::today());
    }

    /**
     * @return HasOne
     */
    public function memberRequest()
    {
        return $this->hasOne(MemberRequest::class, 'member_id', 'clan_id');
    }

    /**
     * @return mixed
     */
    public function activeLeave()
    {
        return $this->hasOne(Leave::class)->where('end_date', '>', \Carbon\Carbon::today());
    }

    /**
     * -------------------------------------
     * Policy object refers to these methods
     * -------------------------------------.
     */

    /**
     * @return BelongsToMany
     */
    public function handles()
    {
        return $this->belongsToMany(Handle::class)->withPivot('value');
    }

    /**
     * @return bool
     */
    public function isSquadLeader(Squad $squad)
    {
        return $this->clan_id === $squad->leader_id;
    }

    /**
     * @return bool
     */
    public function isPlatoonLeader(Platoon $platoon)
    {
        return $this->clan_id === $platoon->leader_id;
    }

    /**
     * Check to see if the member is a division leader
     * and also assigned to the given division.
     *
     * @return bool
     */
    public function isDivisionLeader(Division $division)
    {
        if ($this->division->id === $division->id && \in_array($this->position, [
            Position::EXECUTIVE_OFFICER,
            Position::COMMANDING_OFFICER,
        ], true)) {
            return true;
        }

        return false;
    }

    public function getDiscordUrl()
    {
        if (! $this->discord_id) {
            return false;
        }

        return sprintf('https://discordapp.com/users/%d', $this->discord_id);
    }

    /**
     * @return bool
     */
    public function isRank($rank)
    {
        if (! $this->rank instanceof Rank) {
            return false;
        }
        if (\is_array($rank)) {
            return \in_array(strtolower($this->rank->abbreviation), array_map('strtolower', $rank), true);
        }

        return $this->rank->abbreviation === $rank;
    }

    public function getUrlParams()
    {
        return [$this->clan_id, $this->rank->getAbbreviation() . '-' . $this->name];
    }

    /**
     * @return HasMany
     */
    public function memberRequests()
    {
        return $this->hasMany(MemberRequest::class, 'requester_id', 'clan_id');
    }

    /**
     * Formats member data for a Discord command response.
     */
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
            "TeamSpeak activity: {$this->present()->lastActive('last_ts_activity', ['weeks', 'months'])}",
            "Discord activity: {$this->present()->lastActive('last_voice_activity', ['weeks', 'months'])}",
            'Discord Username: ' . ($this->discord ?? 'Not set'),
        ];

        return [
            'name' => "{$this->present()->rankName} ({$this->clan_id}) - {$division}",
            'value' => 'Profiles: '
                . implode(', ', $links)
                . PHP_EOL
                . implode(PHP_EOL, $properties),
        ];
    }
}
