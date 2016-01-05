<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    /**
     * relationship - division has many members
     */
    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('primary');
    }

    /**
     * relationship - division has many platoons
     */
    public function platoons()
    {
        return $this->hasMany(Platoon::class);
    }

    /**
     * Gets part time members of a division
     */
    public function getPartTimeMembers()
    {
        return $this->members()->wherePivot('primary', false)->get();
    }

    /**
     * Gets active members of a division
     */
    public function getActiveMembers()
    {
        return $this->members()->wherePivot('primary', true);
    }

    /**
     * Gets unassigned members of a division
     * (unassigned meaning no platoon assignment)
     */
    public function getUnassignedMembers()
    {
    	return $this->members()->where(['platoon_id' => 0, 'position_id' => 1]);
    }

    public function leaders()
    {
        return $this->members()->where(['position_id' => 7, 'position_id' => 8]);
    }
}
