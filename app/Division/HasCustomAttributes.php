<?php

namespace App\Division;

use App;

trait HasCustomAttributes
{
    /**
     * Use proper name if it's provided
     * @param $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        // avoid breaking reliance in data sync
        if (App::runningInConsole()) {
            return $value;
        }

        if ($this->proper_name) {
            return $this->proper_name;
        }

        return $value;
    }
}
