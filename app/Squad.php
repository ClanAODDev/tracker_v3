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
     * Leader of a squad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leader()
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
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
}



