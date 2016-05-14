<?php

namespace App\Member;

use Carbon;
use App\Division\Preferences;

trait HasCustomAttributes
{

    public function getAODProfileLinkAttribute()
    {
        return "http://www.clanaod.net/forums/member.php?u=" . $this->clan_id;
    }

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
}