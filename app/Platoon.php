<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Platoon extends Model
{
    /**
     * relationship - platoon belongs to a division
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * relationship - platoon has many squads
     */
    public function squads()
    {
        return $this->hasMany(Squad::class);
    }

    /**
     * relationship - platoon has many members
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
