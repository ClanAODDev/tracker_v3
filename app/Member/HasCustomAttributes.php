<?php

namespace App\Member;

use Carbon\Carbon;

trait HasCustomAttributes
{

    public function getAODProfileLinkAttribute()
    {
        return "http://www.clanaod.net/forums/member.php?u=" . $this->clan_id;
    }

    public function getLastTsActivityAttribute($value)
    {
        if ($value == "0000-00-00 00:00:00") {
            return "<span class='text-danger'>UNIQUE ID MISMATCH</span>";
        }

        if ($value == null) {
            return "<span class='text-danger'>NO TS UNIQUE ID SET</span>";
        }

        return Carbon::parse($value)->diffInDays();
    }

    /**
     * Accessor for name - enforce proper casing
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
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
