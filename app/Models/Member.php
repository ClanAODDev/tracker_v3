<?php

namespace App\Models;

use App\Activities\RecordsActivity;
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
class Member extends \Illuminate\Database\Eloquent\Model
{
    use HasCustomAttributes;
    use HasFactory;
    use RecordsActivity;
    use SoftDeletes;

    public const UNVERIFIED_EMAIL_GROUP_ID = 3;

    protected static array $recordEvents = [];

    protected $casts = [
        'pending_member' => 'boolean',
        'flagged_for_inactivity' => 'boolean'
    ];

    protected $guarded = [];

    protected $dates = [
        'join_date',
        'last_activity',
        'last_ts_activity',
        'last_promoted_at',
        // sgt info
        'last_trained_at',
        'xo_at',
        'co_at',
    ];

    public function present(): MemberPresenter
    {
        return new \App\Presenters\MemberPresenter($this);
    }

    public function user()
    {
        return $this->hasOne(\App\Models\User::class);
    }

    public function transfers()
    {
        return $this->hasMany(\App\Models\Transfer::class, 'member_id')
            ->orderBy('created_at', 'desc');
    }

    public function rankActions()
    {
        return $this->hasMany(\App\Models\RankAction::class, 'member_id')
            ->orderBy('created_at', 'desc');
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\Note::class, 'member_id')->orderBy('created_at', 'desc');
    }

    public function assignPosition($position): Model
    {
        $newPosition = $position instanceof \App\Models\Position ? $position : \App\Models\Position::whereName(strtolower($position))->firstOrFail();
        // reset assignments for specific positions
        if (\in_array(
            $newPosition->name,
            ['Commanding Officer', 'Executive Officer', 'General Sergeant', 'Clan Admin'],
            true
        )) {
            $this->platoon_id = 0;
            $this->squad_id = 0;
        }

        if ('Executive Officer' === $newPosition->name) {
            $this->xo_at = now();
            $this->co_at = null;
        }
        if ('Commanding Officer' === $newPosition->name) {
            $this->co_at = now();
            $this->xo_at = null;
        }

        return $this->position()->associate($newPosition);
    }

    /**
     * relationship - member belongs to a position.
     */
    public function position()
    {
        return $this->belongsTo(\App\Models\Position::class);
    }

    /**
     * @param $rank
     * @return Model
     */
    public function assignRank($rank)
    {
        return $this->rank()->associate(\App\Models\Rank::whereName(strtolower($rank))->firstOrFail());
    }

    /**
     * relationship - member belongs to a rank.
     */
    public function rank()
    {
        return $this->belongsTo(\App\Models\Rank::class);
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
        return $this->hasOne(\App\Models\Squad::class, 'leader_id');
    }

    /**
     * Resets member's positions and division assignments
     * including part-time divisions.
     */
    public function resetPositionAndAssignments()
    {
        $this->update([
            'division_id' => 0, 'platoon_id' => 0, 'squad_id' => 0, 'position_id' => 1,
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
        return $this->belongsToMany(\App\Models\Division::class, 'division_parttimer')->withTimestamps();
    }

    /**
     * relationship - member belongs to a platoon.
     */
    public function platoon()
    {
        return $this->belongsTo(\App\Models\Platoon::class);
    }

    /**
     * relationship - member belongs to a squad.
     */
    public function squad()
    {
        return $this->belongsTo(\App\Models\Squad::class);
    }

    /**
     * relationship - member has many divisions.
     */
    public function division()
    {
        return $this->belongsTo(\App\Models\Division::class);
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
        return $this->hasOne(\App\Models\Leave::class, 'member_id', 'clan_id');
    }

    /**
     * @return mixed
     */
    public function expiredLeave()
    {
        return $this->hasOne(\App\Models\Leave::class)->where('end_date', '<', \Carbon\Carbon::today());
    }

    /**
     * @return HasOne
     */
    public function memberRequest()
    {
        return $this->hasOne(\App\Models\MemberRequest::class, 'member_id', 'clan_id');
    }

    /**
     * @return mixed
     */
    public function activeLeave()
    {
        return $this->hasOne(\App\Models\Leave::class)->where('end_date', '>', \Carbon\Carbon::today());
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
        return $this->belongsToMany(\App\Models\Handle::class)->withPivot('value');
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
        if ($this->division->id === $division->id && \in_array($this->position_id, [5, 6], true)) {
            return true;
        }

        return false;
    }

    public function getDiscordUrl()
    {
        if (!$this->discord)
            return false;

        return sprintf("https://discordapp.com/users/%d", $this->discord);
    }

    /**
     * @param $rank
     * @return bool
     */
    public function isRank($rank)
    {
        if (!$this->rank instanceof \App\Models\Rank) {
            return false;
        }
        if (\is_array($rank)) {
            return \in_array(strtolower($this->rank->abbreviation), array_map('strtolower', $rank), true);
        }

        return $this->rank->abbreviation === $rank;
    }

    public function getUrlParams()
    {
        return [$this->clan_id, $this->rank->abbreviation . '-' . $this->name];
    }

    /**
     * @return HasMany
     */
    public function memberRequests()
    {
        return $this->hasMany(\App\Models\MemberRequest::class, 'requester_id', 'clan_id');
    }
}
