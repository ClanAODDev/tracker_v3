<?php

namespace App\Member;

use Carbon\Carbon;

trait HasCustomAttributes
{
    public function getAODProfileLinkAttribute()
    {
        return "http://www.clanaod.net/forums/member.php?u=" . $this->clan_id;
    }

    public function getTsInvalidAttribute()
    {
        return (carbon_date_or_null_if_zero($this->last_ts_activity) == null);
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
     * Is a pending member?
     * @return mixed
     */
    public function getIsPendingAttribute()
    {
        return $this->pending_member;
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
