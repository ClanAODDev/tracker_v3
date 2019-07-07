<?php

namespace App;

use App\Activities\RecordsActivity;
use App\Presenters\SquadPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Squad extends Model
{

    use RecordsActivity;
    use SoftDeletes;

    protected static $recordEvents = [
        'created',
        'updated',
        'deleted'
    ];

    protected $fillable = [
        'leader_id',
        'name'
    ];

    /**
     * @return SquadPresenter
     */
    public function present()
    {
        return new SquadPresenter($this);
    }

    /**
     * relationship - squad belongs to a platoon
     */
    public function platoon()
    {
        return $this->belongsTo(Platoon::class);
    }

    /**
     * relationship - squad has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class)
            ->orderBy('rank_id', 'desc')
            ->orderBy('name', 'asc');
    }

    /**
     * Assign the leader of a squad
     *
     * @param $member
     * @return Model
     */
    public function assignLeaderTo($member)
    {
        return $this->leader()->associate($member);
    }

    /**
     * Leader of a squad
     *
     * @return BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }
}
