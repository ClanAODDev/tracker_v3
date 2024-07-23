<?php

namespace App\Models;

use App\Presenters\RolePresenter;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    public function present(): RolePresenter
    {
        return new RolePresenter($this);
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }
}
