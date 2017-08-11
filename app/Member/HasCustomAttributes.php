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
        if ($this->last_ts_activity == "0000-00-00 00:00:00") {
            return "UNIQUE ID MISMATCH";
        }

        if ($this->last_ts_activity == null) {
            return "NO TS UNIQUE ID";
        }

        return false;
    }

    public function getMismatchedTeamspeakIdAttribute()
    {
        return $this->last_ts_activity == '0000-00-00 00:00:00';
    }

    public function getNullTeamspeakIdAttribute()
    {
        return $this->last_ts_activity == null;
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
