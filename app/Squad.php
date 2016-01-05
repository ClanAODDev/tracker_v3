<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Squad extends Model
{
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
        return $this->hasMany('App\Member');
    }
}
