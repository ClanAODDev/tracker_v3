<?php

namespace App\Division;

trait HasCustomAttributes
{
    /**
     * Get division structure attribute as a proper URL
     *
     * @param $value
     * @return string
     */
    public function getDivisionStructureAttribute($value)
    {
        return "http://www.clanaod.net/forums/showthread.php?t=" . $value;
    }

    /**
     * Get welcome thread attribute as a proper URL
     *
     * @param $value
     * @return string
     */
    public function getWelcomeForumAttribute($value)
    {
        return "http://www.clanaod.net/forums/forumdisplay.php?f=" . $value;
    }
}