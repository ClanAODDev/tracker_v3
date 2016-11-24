<?php

namespace App;

use Exception;
use App\Settings\DivisionSettings;
use App\Activities\RecordsActivity;
use App\Presenters\DivisionPresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Division extends Model
{

    protected $casts = [
        'active' => 'boolean',
        'settings' => 'json',
    ];

    protected $guarded = [
        'id',
    ];

    use Division\HasCustomAttributes;
    use RecordsActivity;

    protected static $recordEvents = ['created', 'updated', 'deleted'];

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

    public function locality($string)
    {

        $locality = collect($this->settings()->locality);

        if ( ! $locality->count()) {
            Log::error("No locality defaults were found for division {$this->name}");

            return $string;
        }

        $results = $locality->first(function ($translation) use ($string) {
            if (array_key_exists('old-string', $translation)) {
                return $translation['old-string'] == $string;
            }
        });

        if ( ! $results) {
            Log::error("The {$string} locality does not exist");

            return $string;
        }

        return $results['new-string'];
    }

    /**
     * Gets CO and XOs of a division
     *
     * @return mixed
     */
    public function leaders()
    {
        return $this->activeMembers()
            ->whereIn('position_id', [5, 6]);

    }

    public function isActive()
    {
        return $this->active;
    }

}
