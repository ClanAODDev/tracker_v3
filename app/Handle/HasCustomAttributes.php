<?php

namespace App\Handle;

trait HasCustomAttributes
{
    public function getFullUrlAttribute()
    {
        return $this->url . $this->pivot->value;
    }
}