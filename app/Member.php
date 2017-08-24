<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Presenters\MemberPresenter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Member
 *
 * @package App
 */
class Member extends Model
{

    use Member\HasCustomAttributes,
        RecordsActivity,
        SoftDeletes;

    /**
     * @var array
     */
    protected static $recordEvents = [
        'created',
    ];

    protected $casts = [
        'pending_member' => 'boolean',
        'flagged_for_inactivity' => 'boolean'
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'clan_id',
        'platoon_id',
        'squad_id',
        'position_id',
        'division_id',
        'posts',
        'join_date',
        'last_activity',
        'last_ts_activity',
        'last_promoted',
        'recruiter_id'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'join_date',
        'last_activity',
        'last_promoted',
    ];

    /**
     * @return MemberPresenter
     */
    public function present()
    {
        return new MemberPresenter($this);
    }

    /**
     * relationship - user has one member
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(Note::class, 'member_id', 'clan_id')->orderBy('created_at', 'desc');
    }

    /**
     * @param $position
     * @return Model
     */
    public function assignPosition($position)
    {
        if ($position instanceof Position) {
            return $this->position()->associate($position);
        }

        // reset assignments for specific positions
        if (in_array($this->position->name, [
            "Commanding Officer",
            "Executive Officer",
            "General Sergeant",
            "Clan Admin",
        ])) {
            $this->platoon_id = 0;
            $this->squad_id = 0;
        }

        return $this->position()->associate(
            Position::whereName(strtolower($position))->firstOrFail()
        );
    }

    /**
     * relationship - member belongs to a position
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @param $rank
     * @return Model
     */
    public function assignRank($rank)
    {
        return $this->rank()->associate(
            Rank::whereName(strtolower($rank))->firstOrFail()
        );
    }

    /**
     * relationship - member belongs to a rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }



    /**
     * Enforce a singleton relationship for squad leaders
     *
     * Prevents members from being a squad leader of more than one squad
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function squadLeaderOf()
    {
        return $this->hasOne(Squad::class, 'leader_id');
    }

    /**
     * Handle Staff Sergeant assignments
     * division/
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ssgtAssignment()
    {
        return $this->belongsToMany(Division::class, 'division_staffSergeants')
            ->withTimestamps();
    }

    /**
     * Resets member's positions and division assignments
     * including part-time divisions
     */
    public function resetPositionsAndAssignments()
    {
        $this->partTimeDivisions()->sync([]);
        $this->division()->dissociate();
        $this->platoon()->dissociate();
        $this->squad()->dissociate();
        $this->position_id = 1;

        $this->flagged_for_inactivity = false;

        $this->save();
    }

    /**
     * relationship - member belongs to a platoon
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    /**
     * relationship - member belongs to a squad
     */
    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * Handle Staff Sergeant assignments
     * division/
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function partTimeDivisions()
    {
        return $this->belongsToMany(Division::class, 'division_parttimer')
            ->withTimestamps();
    }

    /**
     * relationship - member has many divisions
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recruiter()
    {
        return $this->belongsTo(Member::class, 'recruiter_id', 'clan_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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
        return $this->hasOne(Leave::class)
            ->where('end_date', '<', Carbon::today());
    }

    /**
     * @return mixed
     */
    public function activeLeave()
    {
        return $this->hasOne(Leave::class)
            ->where('end_date', '>', Carbon::today());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function handles()
    {
        return $this->belongsToMany(Handle::class)->withPivot('value');
    }


    /**
     * -------------------------------------
     * Policy object refers to these methods
     * -------------------------------------
     */

    /**
     * @param Squad $squad
     * @return bool
     */
    public function isSquadLeader(Squad $squad)
    {
        return $this->clan_id === $squad->leader_id;
    }

    /**
     * @param Platoon $platoon
     * @return bool
     */
    public function isPlatoonLeader(Platoon $platoon)
    {
        return $this->clan_id === $platoon->leader_id;
    }

    /**
     * Check to see if the member is a division leader
     * and also assigned to the given division
     *
     * @param Division $division
     * @return bool
     */
    public function isDivisionLeader(Division $division)
    {
        if ($this->division->id === $division->id &&
            in_array($this->position_id, [5, 6])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $rank
     * @return bool
     */
    public function isRank($rank)
    {
        if ( ! $this->rank instanceof Rank) {
            return false;
        }

        if (is_array($rank)) {
            return in_array(
                strtolower($this->rank->abbreviation),
                array_map('strtolower', $rank)
            );
        }

        return $this->rank->abbreviation === $rank;
    }
}
