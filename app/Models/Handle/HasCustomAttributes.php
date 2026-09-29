<?php

namespace App\Models\Handle;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasCustomAttributes
{
    protected function fullUrl(): Attribute
    {
        return Attribute::get(fn () => $this->url && $this->pivot ? $this->url . urlencode($this->pivot->value) : null);
    }
}
