<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Presenters\MemberPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Member
 *
 * @package App
 */
class Member extends Model
{

    use Member\HasCustomAttributes;
    use RecordsActivity;
    use SoftDeletes;

    /**
     * @var array
     */
    protected static $recordEvents = [
        'created',
    ];

    /**
     * @var array
     */
    protected $guarded = ['id'];

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
        return $this->belongsToMany(Division::class, 'staff_sergeants');
    }

    /**
     * Resets member's positions and division assignments
     * including part-time divisions
     */
    public function resetPositionsAndAssignments()
    {
        $this->divisions()->detach();
        $this->position()->dissociate();
        $this->platoon()->dissociate();
        $this->squad()->dissociate();

        $this->save();
    }

    /**
     * relationship - member has many divisions
     */
    public function divisions()
    {
        return $this->belongsToMany(Division::class)
            ->withPivot('primary')->withTimestamps();
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recruiter()
    {
        return $this->belongsTo(Member::class, 'recruiter_id', 'clan_id');
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
        if ($this->primaryDivision->id === $division->id &&
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
