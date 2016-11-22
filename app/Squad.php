<?php

namespace App;

use App\Activities\RecordsActivity;
use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{

    use RecordsActivity;
    
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
        return $this->hasMany(Member::class);
    }

    /**
     * List members of a squad without the leader
     *
     * @return mixed
     */
    public function membersWithoutLeader()
    {
        return $this->hasMany(Member::class)->whereNotIn('clan_id', [$this->leader->id]);
    }

    /**
     * Leader of a squad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'clan_id', 'leader_id');

        // $squad->members()->whereNotIn('id', [$squad->leader->id])->get();
    }

    public function assignLeaderTo($member)
    {
        return $this->leader()->associate($member);
    }
}



