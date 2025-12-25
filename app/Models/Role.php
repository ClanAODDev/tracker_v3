<?php

namespace App\Models;

use App\Presenters\RolePresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $guarded = [];

    public function present(): RolePresenter
    {
        return new RolePresenter($this);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
