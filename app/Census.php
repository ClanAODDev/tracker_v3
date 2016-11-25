<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Census extends Model
{
    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
