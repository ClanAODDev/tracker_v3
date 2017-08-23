<?php

namespace App\Census;

use Carbon\Carbon;

trait HasCustomAttributes
{
    public function getJavascriptTimestampAttribute()
    {
      return $this->created_at->timestamp * 1000;
    }
}