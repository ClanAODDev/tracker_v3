<?php

namespace App;

use App\Presenters\RolePresenter;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function present()
    {
        return new RolePresenter($this);
    }

    /**
     * relationship - role belongs to many users
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
