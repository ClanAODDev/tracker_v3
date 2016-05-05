<?php
/**
 * Created by PhpStorm.
 * User: dcdeaton
 * Date: 5/5/2016
 * Time: 12:50 PM
 */

namespace App\Member;

use Carbon;
use App\Division\Preferences;

trait HasCustomAttributes
{

    /**
     * Returns member's name with position icon
     *
     * @return string
     */
    public function getSpecialNameAttribute()
    {
        if ($this->position) {
            $title = ($this->position->name) ?: null;
            $icon = ($this->position->icon) ? "<i class=\"fa fa-{$this->position->icon}\"></i>" : null;

            return "<span title=\"{$title}\" class=\"{$this->position->class}\">{$icon} {$this->name}</span>";
        }

        return $this->name;
    }

    /**
     * Accessor for name - enforce proper casing
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Format join date as a carbon instance
     *
     * @param $value
     * @return string
     */
    public function getJoinDateAttribute($value)
    {
        return Carbon::parse($value)->toFormattedDateString();
    }

    /**
     * Gets member's rank and name
     *
     * @return string
     */
    public function getRankNameAttribute()
    {
        return $this->rank->abbreviation . " " . $this->name;
    }

    /**
     * Gets member's primary division
     */
    public function getPrimaryDivisionAttribute()
    {
        return $this->divisions()->wherePivot('primary', true)->first();
    }

    /**
     * Gets member's activity information
     *
     * @return mixed
     */
    public function getActivityAttribute()
    {
        $preferences = Preferences::ActivityThreshold();
        $days = $this->last_forum_login->diffInDays();

        foreach ($preferences as $limit) {
            if ($days > $limit['days']) {
                return $limit;
            }
        }
    }
}