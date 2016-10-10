<?php

namespace App;

use App\Activity;
use App\Settings\DivisionSettings;
use App\Presenters\DivisionPresenter;
use App\Settings\LocalitySettings;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'json',
        'locality' => 'json',
    ];

    protected $guarded = [
        'id',
    ];

    use Division\HasCustomAttributes;

    /**
     * @return DivisionPresenter
     */
    public function present()
    {
        return new DivisionPresenter($this);
    }

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
     * Division has many platoons
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function platoons()
    {
        return $this->hasMany(Platoon::class);
    }


    /**
     * Division has many activity entries
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activity()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Enabled division scope
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereActive(true);
    }

    /**
     * Gets part time members of a division
     */
    public function partTimeMembers()
    {
        return $this->members()->wherePivot('primary', false);
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

    public function staffSergeants()
    {
        return $this->belongsToMany(Member::class, 'staff_sergeants');
    }

    /**
     * Gets unassigned members of a division (no platoon assignment)
     * NOTE: Only members (position 1)
     */
    public function unassignedMembers()
    {
        return $this->members()
            ->where('platoon_id', 0)
            ->whereNotIn('position_id', [

            ]);
    }

    /**
     * @return DivisionSettings
     */
    public function settings()
    {
        return new DivisionSettings($this->settings, $this);
    }

    public function locality()
    {
        return new LocalitySettings($this->locality, $this);
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

    public function isActive()
    {
        return $this->active;
    }
}
