<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * relationship - user has one role
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }
}
