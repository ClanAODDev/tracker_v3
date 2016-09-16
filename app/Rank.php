<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    /**
     * relationship - rank has one member
     */
    public function member()
    {
        return $this->hasOne(Member::class);
    }
}
