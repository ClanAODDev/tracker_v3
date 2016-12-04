<?php

namespace App\Member;

use Carbon\Carbon;
use Exception;
use App\Division;

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
        if ( ! $this->primaryDivision instanceof Division) {
            throw new Exception("Member {$this->id} has no primary division");
        }

        $days = $this->last_forum_login->diffInDays();
        $limits = $this->primaryDivision
            ->settings()
            ->get('activity_threshold');

        foreach ($limits as $limit) {
            if ($days >= $limit['days']) {
                return $limit;
            }
        }

        return [
            'class' => 'text-success'
        ];
    }

    /**
     * Accessor for name - enforce proper casing
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Gets member's primary division
     */
    public function getPrimaryDivisionAttribute()
    {
        return $this->divisions()->wherePivot('primary', true)->first();
    }

    /**
     * @param $value
     * @return string
     */
    public function getLastPromotedAttribute($value)
    {
        if (strlen($value)) {
            return Carbon::parse($value)->format('Y-m-d');
        }

        return "Never";

    }

    /**
     * @param $value
     * @return string
     */
    public function getJoinDateAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
