<?php

namespace App\Models\Census;

trait HasCustomAttributes
{
    public function getJavascriptTimestampAttribute()
    {
        return $this->created_at
            ->subDays(1)
            ->timestamp * 1000;
    }
}
