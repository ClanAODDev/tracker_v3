<?php

namespace App;

use Carbon;
use App\Activities\RecordsActivity;
use App\Presenters\MemberPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{

    use Member\HasCustomAttributes;
    use RecordsActivity;
    use SoftDeletes;

    protected static $recordEvents = ['created', 'updated'];

    protected $guarded = ['id'];

    protected $dates = [
        'join_date',
        'last_forum_login',
        'last_promoted',
    ];

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
     * relationship - member has many divisions
     */
    public function divisions()
    {
        return $this->belongsToMany(Division::class)->withPivot('primary')->withTimestamps();
    }

    public function primaryDivision()
    {
        return $this->hasOne(Division::class)->wherePivot('primary', true);
    }

    /**
     * relationship - member belongs to a platoon
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    /**
     * relationship - member belongs to a rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * relationship - member belongs to a position
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * relationship - member belongs to a squad
     */
    public function squad()
    {
        return $this->belongsTo(Squad::class);
    }

    /**
     * Enforce a singleton relationship for squad leaders
     *
     * Prevents members from being a squad leader of more than one squad
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function squadLeaderOf()
    {
        return $this->hasOne(Squad::class, 'leader_id');
    }

    /**
     * Handle Staff Sergeant assignments
     * division/
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ssgtAssignment()
    {
        return $this->belongsToMany(Division::class, 'staff_sergeants');
    }

    //public function isSquadLeader($)

    /**
     * Policy object refers to these methods
     * @param Squad $squad
     * @return bool
     */
    // MemberPolicy > modify
    // -- Member->isSquadLeader
    public function isSquadLeader(Squad $squad)
    {
        return $this->id === $squad->leader_id;
    }

    public function isPlatoonLeader(Platoon $platoon)
    {
        return $this->id === $platoon->leader_id;
    }

    public function isDivisionLeader(Division $division)
    {
        //return $this->divisions
          //  ->contains($division)
    }
    // -- Member->isDivisionLeader
}
