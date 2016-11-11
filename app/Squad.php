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

    public function scopeMembersWithoutLeader($query, $squadLeaderId)
    {
        return $query->where('id', '!=', $squadLeaderId);
    }

    public function leader()
    {
        return $this->belongsTo('App\Member', 'leader_id');
    }

    public function assignLeaderTo($member)
    {
        return $this->leader()->associate($member);
    }
}
