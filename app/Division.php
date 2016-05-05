<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{

    use Division\HasCustomAttributes;

    /**
     * Get division's squads
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function squads()
    {
        return $this->hasManyThrough(Squad::class, Platoon::class);
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
    public function partTimeMembers()
    {
        return $this->members()->wherePivot('primary', false)->get();
    }

    /**
     * relationship - division has many members
     */
    public function members()
    {
        return $this->belongsToMany(Member::class)->withPivot('primary')->withTimestamps();
    }

    /**
     * Gets active members of a division
     */
    public function activeMembers()
    {
        return $this->members()->wherePivot('primary', true);
    }

    /**
     * Gets unassigned members of a division (no platoon assignment)
     * NOTE: Only members (position 1)
     */
    public function unassignedMembers()
    {
        return $this->members()->where([
            'platoon_id' => 0,
            'position_id' => 1
        ]);
    }

    /**
     * Gets CO and XOs of a division
     *
     * @return mixed
     */
    public function leaders()
    {
        return $this->members()
            ->where('position_id', 7)
            ->orWhere('position_id', 8);
    }
}
