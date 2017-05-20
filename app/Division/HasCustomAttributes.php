<?php

namespace App\Division;

use App;

trait HasCustomAttributes
{
    public function getAbbreviationAttribute($value)
    {
        return strtoupper($value);
    }
}
