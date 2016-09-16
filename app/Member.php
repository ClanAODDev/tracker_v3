<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Presenters\MemberPresenter;
use Carbon;
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

    public function squadLeaderOf()
    {
        return $this->belongsTo(Squad::class, 'leader_id');
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
}
