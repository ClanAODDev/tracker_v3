<?php

namespace App\Member;

use Carbon;

trait HasCustomAttributes
{

    public function getAODProfileLinkAttribute()
    {
        return "http://www.clanaod.net/forums/member.php?u=" . $this->clan_id;
    }

    /**
     * Classifies activity level based on division threshold
     *
     * @return array
     */
    public function getActivityAttribute()
    {
        $days = $this->last_forum_login->diffInDays();
        $settings = $member->primaryDivision->settings();

        foreach ($settings->get('activity_threshold') as $limit) {
            if ($days < $limit['days']) {
                return $limit;
            }

            return [
                'class' => 'fa fa-success'
            ];
        }
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
     * Gets member's primary division
     */
    public function getPrimaryDivisionAttribute()
    {
        return $this->divisions()->wherePivot('primary', true)->first();
    }
}
