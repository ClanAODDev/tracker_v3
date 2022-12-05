<?php

namespace App\Models\Handle;

trait HasCustomAttributes
{
    public function getFullUrlAttribute()
    {
        return $this->url.$this->pivot->value;
    }
}
