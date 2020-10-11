<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{

    /**
     * relationship - position has one member
     */
    public function member()
    {
        return $this->hasOne(Member::class);
    }
}
