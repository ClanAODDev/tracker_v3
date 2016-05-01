<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{

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
     * Gets unassigned members of a division
     * (unassigned meaning no platoon assignment)
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

    /**
     * Get division structure attribute as a proper URL
     *
     * @param $value
     * @return string
     */
    public function getDivisionStructureAttribute($value)
    {
        return "http://www.clanaod.net/forums/showthread.php?t=" . $value;
    }

    /**
     * Get welcome thread attribute as a proper URL
     *
     * @param $value
     * @return string
     */
    public function getWelcomeForumAttribute($value)
    {
        return "http://www.clanaod.net/forums/forumdisplay.php?f=" . $value;
    }
}
