<?php

namespace App\Models\Handle;

trait HasCustomAttributes
{
    public function getFullUrlAttribute()
    {
        return $this->url . urlencode($this->pivot->value);
    }
}
